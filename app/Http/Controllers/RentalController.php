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
        // 1. Ambil semua console beserta sesi aktif (dan relasi paket & orders)
        $consoles = Console::with(['sessions' => function ($query) {
            $query->where('status', 'active')->with(['orders.product', 'package']);
        }])->orderBy('id', 'asc')->get();

        // 2. Ambil produk FnB diurutkan A-Z
        $products = Product::orderBy('name', 'asc')->get();

        // 3. Ambil daftar paket diurutkan berdasarkan durasi terpendek ke terpanjang
        $packages = Package::orderBy('duration_minutes', 'asc')->get();

        return view('dashboard', compact('consoles', 'products', 'packages'));
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

    public function stopSession($id)
    {
        $session = RentalSession::with(['console', 'package', 'orders'])->findOrFail($id);

        $endTime = Carbon::now();
        $startTime = Carbon::parse($session->start_time);
        $totalMinutes = $startTime->diffInMinutes($endTime);

        $rentalCost = 0;

        // --- Logika Perhitungan Biaya Rental ---
        if ($session->type === 'package' && $session->package) {
            // Jika memilih Paket, biaya awal sesuai harga flat paket
            $rentalCost = $session->package->price;
            
            // Opsional: Jika waktu penggunaan melebihi durasi paket (overtime),
            // kelebihannya dihitung per 15 menit menggunakan tarif reguler console
            if ($totalMinutes > $session->package->duration_minutes) {
                $extraMinutes = $totalMinutes - $session->package->duration_minutes;
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

        $session->update([
            'end_time' => $endTime,
            'total_minutes' => $totalMinutes,
            'rental_cost' => $rentalCost,
            'fnb_cost' => $fnbCost,
            'total_cost' => $totalCost,
            'status' => 'completed',
        ]);

        return redirect()->back()->with('success', "Sesi dikembalikan! Total Tagihan: Rp " . number_format($totalCost, 0, ',', '.'));

        
    }
    public function addOrder(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $subtotal = $product->price * $request->quantity;

        Order::create([
            'rental_session_id' => $sessionId,
            'product_id'        => $request->product_id,
            'quantity'          => $request->quantity,
            'price'             => $product->price,
            'subtotal'          => $subtotal,
        ]);

        return redirect()->back()->with('success', 'Pesanan FnB berhasil ditambahkan!');
    }
}