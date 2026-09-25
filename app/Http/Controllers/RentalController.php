<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Console;
use App\Models\Product;
use App\Models\Package;
use App\Models\RentalSession;
use App\Models\Order;
use Carbon\Carbon;

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
            'package_id' => $request->type === 'package' ? $request->package_id : null,
            'type' => $request->type,
            'start_time' => Carbon::now(),
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Sesi rental berhasil dimulai!');
    }



    public function stopSession(Request $request, $id) // <-- Tambahkan parameter Request $request
    {
        // 1. Validasi input metode pembayaran dari form
        $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,qris'],
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
        $totalCost = $rentalCost + $fnbCost;

        // --- Simpan Perubahan Termasuk Payment Method ---
        $session->update([
            'end_time' => $endTime,
            'rental_cost' => $rentalCost,
            'fnb_cost' => $fnbCost,
            'total_cost' => $totalCost,
            'payment_method' => $request->payment_method, // <-- SIMPAN DI SINI
            'status' => 'completed',
        ]);

        // Kembalikan ke dashboard dengan membawa ID sesi yang baru di-stop
        return redirect()->back()->with([
            'success' => 'Sesi rental berhasil diselesaikan!',
            'show_receipt_id' => $session->id
        ]);
    }
    public function addOrder(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // 1. Cek ketersediaan stok
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', "Stok {$product->name} tidak mencukupi! Tersisa: {$product->stock}");
        }

        // 2. Buat order
        $subtotal = $product->price * $request->quantity;

        Order::create([
            'rental_session_id' => $sessionId,
            'product_id'        => $product->id,
            'quantity'          => $request->quantity,
            'price'             => $product->price,
            'subtotal'          => $subtotal,
        ]);

        // 3. Potong stok otomatis
        $product->decrement('stock', $request->quantity);

        return redirect()->back()->with('success', 'Pesanan FnB berhasil ditambahkan!');
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