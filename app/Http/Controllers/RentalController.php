<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Console;
use App\Models\Product;
use App\Models\Package;
use App\Models\RentalSession;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Shift; // 🟢 Jangan lupa import model Shift di bagian atas controller
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
  public function index()
{
    // 1. Ambil semua console beserta sesi aktif
    $consoles = Console::with(['sessions' => function ($query) {
        $query->where('status', 'active')->with(['orders.product', 'package']);
    }])->orderBy('id', 'asc')->get();

    // 2. Ambil produk FnB & Paket
    $products = Product::orderBy('name', 'asc')->get();
    $packages = Package::all();

    // 🟢 3. TAMBAHKAN BAGIAN INI (Cek apakah ada request cetak/tampil struk)
    $receiptSession = null;
    if (session('show_receipt_id')) {
        $receiptSession = RentalSession::with(['console', 'package', 'orders.product'])
            ->find(session('show_receipt_id'));
    }

    // 🟢 4. Masukkan 'receiptSession' ke dalam compact()
    return view('dashboard', compact('consoles', 'products', 'packages', 'receiptSession'));
}

    public function startSession(Request $request)
    {
        // 🟢 1. Cek apakah ada shift yang sedang AKTIF untuk user/kasir yang login
        $activeShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->back()->with('error', 'Gagal memulai rental! Kamu harus Buka Shift terlebih dahulu.');
        }

        $request->validate([
            'console_id' => 'required|exists:consoles,id',
            'type' => 'required|in:open,package',
            'package_id' => 'nullable|required_if:type,package|exists:packages,id',
        ]);

        // Pastikan tidak ada sesi aktif di console yang sama
        $activeSession = RentalSession::where('console_id', $request->console_id)
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return redirect()->back()->with('error', 'Console ini sedang aktif digunakan!');
        }

        RentalSession::create([
            'console_id' => $request->console_id,
            'user_id'    => Auth::id(),        // 🟢 Catat siapa kasir yang melayani
            'shift_id'   => $activeShift->id,  // 🟢 KUNCI DI SINI: Kaitkan transaksi ke Shift Aktif
            'package_id' => $request->type === 'package' ? $request->package_id : null,
            'type'       => $request->type,
            'start_time' => Carbon::now(),
            'status'     => 'active',
        ]);

        return redirect()->back()->with('success', 'Sesi rental berhasil dimulai!');
    }



    public function stopSession(Request $request, $id)
    {
        // 1. Validasi input metode pembayaran (ditambahkan opsi 'split')
        $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,qris,split'],
            'cash_amount' => ['required_if:payment_method,split', 'nullable', 'numeric', 'min:0'],
            'qris_amount' => ['required_if:payment_method,split', 'nullable', 'numeric', 'min:0'],
        ]);

        $session = RentalSession::with(['console', 'package', 'orders'])->findOrFail($id);

        $endTime = Carbon::now();
        $startTime = Carbon::parse($session->start_time);
        $totalMinutes = $startTime->diffInMinutes($endTime);

        $rentalCost = 0;

        // --- Logika Perhitungan Biaya Rental ---
        if ($session->type === 'package' && $session->package) {
            // 1. Biaya awal paket
            $rentalCost = $session->package->price;
            
            // 2. Tambahkan biaya dari perpanjangan paket (jika ada)
            $rentalCost += $session->extended_cost;

            // 3. Total batas durasi resmi (Durasi Paket Awal + Perpanjangan)
            $allowedDuration = $session->package->duration_minutes + $session->extended_minutes;

            // 4. Cek overtime jika waktu main riil melebihi batas durasi resmi
            if ($totalMinutes > $allowedDuration) {
                $extraMinutes = $totalMinutes - $allowedDuration;
                $extraBlocks = floor($extraMinutes / 15);
                $ratePerBlock = $session->console->hourly_rate / 4;
                $rentalCost += ($extraBlocks * $ratePerBlock);
            }
        } else {
            // Jika Open Play, dihitung per blok 15 menit
            $billableBlocks = floor($totalMinutes / 15);
            $ratePerBlock = $session->console->hourly_rate / 4;
            $rentalCost = $billableBlocks * $ratePerBlock;
        }

        // --- Hitung Total FnB & Grand Total ---
        $fnbCost = $session->orders->sum('subtotal');
        $grandTotal = $rentalCost + $fnbCost;

        // --- Pembagian Nominal Cash & QRIS (Split Payment) ---
        $paymentMethod = $request->payment_method;
        $cashAmount = 0;
        $qrisAmount = 0;

        if ($paymentMethod === 'cash') {
            $cashAmount = $grandTotal;
            $qrisAmount = 0;
        } elseif ($paymentMethod === 'qris') {
            $cashAmount = 0;
            $qrisAmount = $grandTotal;
        } elseif ($paymentMethod === 'split') {
            $cashAmount = (float) $request->cash_amount;
            $qrisAmount = (float) $request->qris_amount;

            // Validasi: Jumlah gabungan Cash + QRIS tidak boleh kurang dari Total Biaya
            if (($cashAmount + $qrisAmount) < $grandTotal) {
                return redirect()->back()->with('error', 'Jumlah pembayaran (Cash + QRIS) kurang dari total tagihan (Rp ' . number_format($grandTotal, 0, ',', '.') . ')!');
            }
        }

        // --- Simpan Perubahan Sesi Rental ---
        $session->update([
            'end_time'       => $endTime,
            'rental_cost'    => $rentalCost,
            'fnb_cost'       => $fnbCost,
            'total_cost'     => $grandTotal,
            'payment_method' => $paymentMethod,
            'cash_amount'    => $cashAmount, // 🟢 Menyimpan pecahan nominal cash
            'qris_amount'    => $qrisAmount, // 🟢 Menyimpan pecahan nominal qris
            'status'         => 'completed',
        ]);

        // Kembalikan ke dashboard dengan membawa ID sesi yang baru di-stop
        return redirect()->back()->with([
            'success'         => 'Sesi rental berhasil diselesaikan!',
            'show_receipt_id' => $session->id
        ]);
    }
    public function addOrder(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $session = RentalSession::findOrFail($sessionId);
        $product = Product::findOrFail($request->product_id);

        // Cek ketersediaan stok
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi.');
        }

        // 🟢 1. Cek apakah produk ini SUDAH ADA di sesi aktif ini
        $existingOrder = Order::where('rental_session_id', $session->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingOrder) {
            // Jika sudah ada, cukup tambahkan quantity & subtotalnya
            $existingOrder->quantity += $request->quantity;
            $existingOrder->subtotal += ($product->price * $request->quantity);
            $existingOrder->save();
        } else {
            // Jika belum ada, buat record order baru
            Order::create([
                'rental_session_id' => $session->id,
                'product_id'        => $product->id,
                'quantity'          => $request->quantity,
                'price'             => $product->price,
                'subtotal'          => $product->price * $request->quantity,
            ]);
        }

        // Kurangi stok produk
        $product->decrement('stock', $request->quantity);

        return redirect()->back()->with('success', 'Berhasil menambahkan pesanan FnB.');
    }

    public function deleteOrder($id)
    {
        // 1. Cari data order beserta relasi produknya
        $order = Order::with('product')->findOrFail($id);

        // 2. Pastikan sesi rental-nya masih aktif (belum di-stop)
        if ($order->rentalSession && $order->rentalSession->status !== 'active') {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dihapus karena sesi rental sudah selesai.');
        }

        // 🟢 3. Kembalikan stok produk jika relasi produk ditemukan
        if ($order->product) {
            $order->product->increment('stock', $order->quantity);
        }

        // 4. Hapus data order dari database
        $order->delete();

        return redirect()->back()->with('success', 'Pesanan FnB berhasil dibatalkan dan stok telah dikembalikan.');
    }
    
    public function printReceipt($id)
    {
        // Ambil data sesi rental beserta relasi console, paket, dan orders FnB
        $session = RentalSession::with(['console', 'package', 'orders.product'])->findOrFail($id);

        return view('rental.receipt', compact('session'));
    }
    public function extendSession(Request $request, $sessionId)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $session = RentalSession::where('status', 'active')->findOrFail($sessionId);
        $package = Package::findOrFail($request->package_id);

        // Update durasi, biaya, dan nama paket perpanjangan
        $session->increment('extended_minutes', $package->duration_minutes);
        $session->increment('extended_cost', $package->price);
        
        // Simpan/gabungkan nama paket perpanjangan
        $currentExtendedName = $session->extended_package_name 
            ? $session->extended_package_name . ', ' . $package->name 
            : $package->name;

        $session->update([
            'extended_package_name' => $currentExtendedName
        ]);

        return redirect()->back()->with('success', 'Paket berhasil diperpanjang!');
    }
}