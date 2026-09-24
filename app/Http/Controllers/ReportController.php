<?php

namespace App\Http\Controllers;

use App\Models\RentalSession;
use Illuminate\Http\Request;

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

        return view('reports.transactions', compact(
            'sessions',
            'totalRental',
            'totalFnB',
            'grandTotal',
            'startDate',
            'endDate',
            'errorMessage'
        ));
    }
}