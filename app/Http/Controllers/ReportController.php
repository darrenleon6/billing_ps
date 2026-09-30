<?php

namespace App\Http\Controllers;

use App\Models\RentalSession;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        // Jika Admin, hitung Omset, HPP, dan Profit. Jika Operator, hanya hitung Omset.
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
            // Untuk Operator: HPP dan Profit diisi 0 (atau tidak dihitung)
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
}