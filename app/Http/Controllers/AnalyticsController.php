<?php

namespace App\Http\Controllers;

use App\Models\RentalSession;
use App\Models\Console;
use App\Models\Product;
use App\Models\Order; // 🟢 1. Import Model Order
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // 🟢 2. Import DB Facade

class AnalyticsController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. KPI Cards
        $activeSessionsCount = RentalSession::where('status', 'active')->count();
        $completedTodayCount = RentalSession::where('status', 'completed')
            ->whereDate('end_time', $today)
            ->count();

        $todayRevenue = RentalSession::where('status', 'completed')
            ->whereDate('end_time', $today)
            ->sum('total_cost');

        $monthlyRevenue = RentalSession::where('status', 'completed')
            ->whereDate('end_time', '>=', $startOfMonth)
            ->sum('total_cost');

        // 2. Grafik Pendapatan 7 Hari Terakhir
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->isoFormat('D MMM');
            
            $dailyTotal = RentalSession::where('status', 'completed')
                ->whereDate('end_time', $date)
                ->sum('total_cost');

            $chartData[] = $dailyTotal;
        }

        // 3. Console Populer
        $popularConsoles = Console::withCount(['rentalSessions' => function ($query) {
            $query->where('status', 'completed');
        }])
        ->orderBy('rental_sessions_count', 'desc')
        ->take(5)
        ->get();

        // 🟢 4. Produk FnB Terlaris (Tren FnB) menggantikan stok menipis
        $popularProducts = Order::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('rentalSession', function($q) {
                $q->where('status', 'completed');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(5)
            ->get();

        return view('reports.analytics', compact(
            'activeSessionsCount',
            'completedTodayCount',
            'todayRevenue',
            'monthlyRevenue',
            'chartLabels',
            'chartData',
            'popularConsoles',
            'popularProducts' // 🟢 Send popularProducts to view
        ));

    }
}