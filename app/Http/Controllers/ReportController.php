<?php

namespace App\Http\Controllers;

use App\Models\RentalSession;
use App\Models\Order;
use App\Models\Console; // Pastikan model Console ada
use App\Models\Product; // Pastikan model Product ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;




class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $errorMessage = null;

        // 🟢 1. JIKA OPERATOR: Paksa filter tanggal ke hari ini saja
        if ($user->role !== 'admin') {
            $startDate = Carbon::today()->toDateString();
            $endDate   = Carbon::today()->toDateString();
        } else {
            // JIKA ADMIN: Ambil dari input filter tanggal
            $startDate = $request->input('start_date');
            $endDate   = $request->input('end_date');
        }

        // Query dasar transaksi completed
        $query = RentalSession::with(['console', 'orders.product'])
            ->where('status', 'completed');

        // 🟢 2. Filter Tanggal
        if ($user->role === 'admin' && ($startDate || $endDate)) {
            if (!$startDate || !$endDate) {
                $errorMessage = 'Kedua tanggal (Dari & Sampai Tanggal) wajib diisi untuk melakukan filter.';
            } elseif ($startDate > $endDate) {
                $errorMessage = 'Dari Tanggal tidak boleh lebih besar dari Sampai Tanggal.';
            } else {
                $query->whereDate('end_time', '>=', $startDate)
                      ->whereDate('end_time', '<=', $endDate);
            }
        } elseif ($user->role !== 'admin') {
            // Untuk Operator, tampilkan khusus transaksi hari ini
            $query->whereDate('end_time', Carbon::today());
        }

        $sessions = $query->latest('end_time')->get();

        // Hitung total ringkasan pendapatan
        $totalRental = $sessions->sum('rental_cost');
        $totalFnB    = $sessions->sum('fnb_cost');
        $grandTotal  = $sessions->sum('total_cost');

        // 🟢 3. Statistik FnB & HPP
        if ($user->role === 'admin') {
            $fnbStats = Order::whereHas('rentalSession', function($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed');
                    if ($startDate && $endDate && $startDate <= $endDate) {
                        $q->whereDate('end_time', '>=', $startDate)
                        ->whereDate('end_time', '<=', $endDate);
                    }
                })
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->selectRaw('
                    SUM(orders.subtotal) as total_omset,
                    SUM(orders.quantity * products.cost_price) as total_hpp,
                    SUM(orders.subtotal - (orders.quantity * products.cost_price)) as total_profit
                ')
                ->first();

            $fnbRevenue = $fnbStats->total_omset ?? 0;
            $fnbHPP     = $fnbStats->total_hpp ?? 0;
            $fnbProfit  = $fnbStats->total_profit ?? 0;
        } else {
            $fnbRevenue = $sessions->sum('fnb_cost');
            $fnbHPP     = 0;
            $fnbProfit  = 0;
        }

        return view('reports.transactions', compact(
            'sessions',
            'totalRental',
            'totalFnB',
            'grandTotal',
            'startDate',
            'endDate',
            'errorMessage',
            'fnbRevenue',
            'fnbHPP',
            'fnbProfit'
        ));
    }


    // 1. Tampilkan Form Edit Transaksi
    public function editTransaction($id)
    {
        // Pastikan hanya admin yang bisa akses
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $session = RentalSession::with(['console', 'orders.product'])->findOrFail($id);
        return view('reports.edit-transaction', compact('session'));
    }

    // 2. Simpan Perubahan Transaksi
    public function updateTransaction(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $session = RentalSession::findOrFail($id);

        $request->validate([
            'rental_cost' => 'required|numeric|min:0',
            'products' => 'nullable|array', // ID produk yang dipilih
            'quantities' => 'nullable|array', // Jumlah masing-masing produk
        ]);

        // 1. Update biaya sewa PS
        $session->rental_cost = $request->rental_cost;

        // 2. Hapus order FnB lama untuk session ini, lalu hitung ulang
        $session->orders()->delete();

        $fnbCost = 0;
        if ($request->has('products')) {
            foreach ($request->products as $index => $productId) {
                $qty = $request->quantities[$index] ?? 0;
                if ($qty > 0 && !empty($productId)) {
                    $product = Product::find($productId);
                    if ($product) {
                        $subtotal = $product->price * $qty;
                        $fnbCost += $subtotal;

                        // Buat / masukkan order baru
                        Order::create([
                            'rental_session_id' => $session->id,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'price' => $product->price,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }
        }

        // 3. Simpan fnb_cost dan hitung grand total baru
        $session->fnb_cost = $fnbCost;
        $session->total_cost = $session->rental_cost + $session->fnb_cost;
        $session->save();

        return redirect()->back()->with('success', 'Transaksi berhasil diperbarui!');
    }

    // 3. Hapus Transaksi
    public function destroyTransaction($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $session = RentalSession::findOrFail($id);
        
        // Hapus relasi order FnB terkait jika ada, lalu hapus session-nya
        $session->orders()->delete();
        $session->delete();

        return redirect()->route('reports.transactions')->with('success', 'Transaksi berhasil dihapus!');
    }

    // ==========================================
    // 🟢 TAMBAHAN: FITUR KIRIM LAPORAN OTOMATIS KE EMAIL
    // ==========================================
    public function sendAutoDailyReport()
    {
        $date = Carbon::today()->toDateString();
        $time = Carbon::now()->format('H:i:s');

        // Ambil data transaksi hari ini
        $sessions = RentalSession::with(['console', 'orders.product'])
            ->where('status', 'completed')
            ->whereDate('end_time', $date)
            ->get();

        $totalRental = $sessions->sum('rental_cost');
        $totalFnB    = $sessions->sum('fnb_cost');
        $grandTotal  = $sessions->sum('total_cost');

        // Ambil status unit PS/Console
        $units = Console::all(); 

        // Ambil data stok produk FnB
        $products = Product::all();

        // Buat File Excel Laporan Transaksi (.xlsx)
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Transaksi Harian
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Transaksi Harian');
        $sheet1->setCellValue('A1', 'ID');
        $sheet1->setCellValue('B1', 'Datetime');
        $sheet1->setCellValue('C1', 'Unit Name');
        $sheet1->setCellValue('D1', 'Unit Type');
        $sheet1->setCellValue('E1', 'TotalRentalCost');
        $sheet1->setCellValue('F1', 'FNB Order');
        $sheet1->setCellValue('G1', 'GrandTotal');

        $row = 2;
        foreach ($sessions as $session) {
            $sheet1->setCellValue("A{$row}", $session->id);
            $sheet1->setCellValue("B{$row}", $session->end_time);
            $sheet1->setCellValue("C{$row}", optional($session->console)->name ?? 'N/A');
            $sheet1->setCellValue("D{$row}", optional($session->console)->type ?? 'PS');
            $sheet1->setCellValue("E{$row}", $session->rental_cost);
            $sheet1->setCellValue("F{$row}", $session->fnb_cost);
            $sheet1->setCellValue("G{$row}", $session->total_cost);
            $row++;
        }

        // Sheet 2: Stok Menu FnB
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Stok Menu FNB');
        $sheet2->setCellValue('A1', 'No');
        $sheet2->setCellValue('B1', 'Nama Menu');
        $sheet2->setCellValue('C1', 'Stok');
        $sheet2->setCellValue('D1', 'Harga');
        $sheet2->setCellValue('E1', 'Kategori');

        $row2 = 2;
        foreach ($products as $index => $prod) {
            $sheet2->setCellValue("A{$row2}", $index + 1);
            $sheet2->setCellValue("B{$row2}", $prod->name);
            $sheet2->setCellValue("C{$row2}", $prod->stock);
            $sheet2->setCellValue("D{$row2}", $prod->price);
            $sheet2->setCellValue("E{$row2}", $prod->category ?? 'umum');
            $row2++;
        }

        $fileName = 'laporan_rental_' . $date . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        
        // Cukup gunakan satu baris writer yang benar ini:
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
        
        // --- AMBIL LINK DARI FILE TEKS DENGAN AMAN ---
        $urlFile = storage_path('app/cloudflared_url.txt');
        
        // Default fallback awal
        $cloudflaredUrl = 'http://localhost:8000';

        if (file_exists($urlFile)) {
            $content = trim(@file_get_contents($urlFile));
            if (!empty($content)) {
                $cloudflaredUrl = $content;
            }
        }
    
        // Kirim Email ke Admin
        $emailTujuan = 'ps.jefelda@gmail.com'; // Ganti dengan email tujuan penerima laporan

        $dataEmail = [
            'date' => $date,
            'time' => $time,
            'rental' => $totalRental,
            'fnb' => $totalFnB,
            'grand_total' => $grandTotal,
            'units' => $units,
            'cloudflaredUrl' => $cloudflaredUrl
        ];

        Mail::send('emails.daily_reports', $dataEmail, function($message) use ($emailTujuan, $filePath, $date) {
            $message->to($emailTujuan)
                    ->subject('LAPORAN RENTAL OTOMATIS Tanggal ' . $date)
                    ->attach($filePath);
        });

        return response()->json(['status' => 'Success', 'message' => 'Laporan otomatis berhasil dikirim ke email!']);
    }

    
}