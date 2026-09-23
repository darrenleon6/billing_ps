<?php

namespace App\Http\Controllers;

use App\Models\Console;
use App\Models\Product;
use App\Models\RentalSession;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RentalController extends Controller
{
    // 1. Tampilan Utama Dashboard Billing
    public function index()
    {
        $consoles = Console::all();
        $products = Product::all();
        $activeSessions = RentalSession::with(['console', 'orders.product'])
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        return view('dashboard', compact('consoles', 'products', 'activeSessions'));
    }

    // 2. Memulai Sesi Rental PS
    public function startSession(Request $request)
    {
        $request->validate([
            'console_id' => 'required|exists:consoles,id',
            'type' => 'required|in:open,package',
        ]);

        $console = Console::findOrFail($request->console_id);

        // Buat sesi rental baru
        RentalSession::create([
            'console_id' => $console->id,
            'type' => $request->type,
            'start_time' => Carbon::now(),
            'status' => 'active',
        ]);

        // Ubah status console jadi sedang dipakai
        $console->update(['status' => 'in_use']);

        return redirect()->back()->with('success', 'Sesi rental berhasil dimulai!');
    }

    // 3. Menambah Pesanan FnB ke Sesi Rental
    public function addOrder(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $session = RentalSession::findOrFail($sessionId);
        $product = Product::findOrFail($request->product_id);

        // Cek apakah stok cukup
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi!');
        }

        $subtotal = $product->price * $request->quantity;

        // Catat order
        Order::create([
            'rental_session_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'price' => $product->price,
            'subtotal' => $subtotal,
        ]);

        // Kurangi stok produk & update total biaya FnB di sesi rental
        $product->decrement('stock', $request->quantity);
        $session->increment('fnb_cost', $subtotal);

        return redirect()->back()->with('success', 'Pesanan FnB berhasil ditambahkan!');
    }

    // 4. Menghentikan Sesi Rental & Hitung Total Tagihan
    // 4. Menghentikan Sesi Rental & Hitung Total Tagihan (Sistem Blok 15 Menit)
    public function stopSession($sessionId)
    {
        $session = RentalSession::with('console')->findOrFail($sessionId);
        $endTime = Carbon::now();

        $startTime = Carbon::parse($session->start_time);
        
        // 1. Hitung total durasi dalam menit
        $totalMinutes = $startTime->diffInMinutes($endTime);

        // 2. Hitung berapa blok 15 menit yang sudah dilewati (dibulatkan ke bawah)
        $blocks = floor($totalMinutes / 15);

        // 3. Hitung tarif per 15 menit (Tarif per jam dibagi 4)
        $ratePerBlock = $session->console->hourly_rate / 4;

        // 4. Biaya rental = Jumlah Blok x Tarif per Blok
        $rentalCost = $blocks * $ratePerBlock;

        // 5. Total tagihan = Biaya Rental + Biaya FnB
        $totalCost = $rentalCost + $session->fnb_cost;

        // Selesaikan sesi
        $session->update([
            'end_time' => $endTime,
            'status' => 'completed',
            'rental_cost' => $rentalCost,
            'total_cost' => $totalCost,
        ]);

        // Kembalikan status console jadi siap dipakai lagi
        $session->console->update(['status' => 'ready']);

        return redirect()->back()->with('success', 'Sesi rental selesai! (' . $blocks . ' blok 15 mnt) Total tagihan: Rp ' . number_format($totalCost, 0, ',', '.'));
    }
}