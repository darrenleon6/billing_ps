<?php

namespace App\Http\Controllers;

use App\Models\RentalSession;
use App\Models\Order; // 🟢 Jangan lupa import model Order
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 🟢 Import DB Facade

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $errorMessage = null;

        // Query dasar transaksi completed
        $query = RentalSession::with(['console', 'orders.product'])
            ->where('status', 'completed');

        // Pengecekan logika manual tanpa memicu redirect
        if ($startDate || $endDate) {
            // Jika salah satu kosong ATAU start_date > end_date
            if (!$startDate || !$endDate) {
                $errorMessage = 'Kedua tanggal (Dari & Sampai Tanggal) wajib diisi untuk melakukan filter.';
            } elseif ($startDate > $endDate) {
                $errorMessage = 'Dari Tanggal tidak boleh lebih besar dari Sampai Tanggal.';
            } else {
                // Jika lolos pengecekan, terapkan filter
                $query->whereDate('end_time', '>=', $startDate)
                      ->whereDate('end_time', '<=', $endDate);
            }
        }

        $sessions = $query->latest('end_time')->get();

        // Hitung total ringkasan pendapatan
        $totalRental = $sessions->sum('rental_cost');
        $totalFnB    = $sessions->sum('fnb_cost');
        $grandTotal  = $sessions->sum('total_cost');

        // 🟢 1. TARUH KUERI FNB STATS DI SINI (SEBELUM RETURN VIEW)
        // Kueri juga mendukung filter tanggal jika sedang digunakan
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

        // Assign variabel hasil kueri
        $fnbRevenue = $fnbStats->total_omset ?? 0;
        $fnbHPP     = $fnbStats->total_hpp ?? 0;
        $fnbProfit  = $fnbStats->total_profit ?? 0;

        // 🟢 2. RETURN VIEW DILETAKKAN PALING BAWAH
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