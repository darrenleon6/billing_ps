<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik & Grafik - Rental PS Kita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    @include('layouts.navigation')

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Statistik & Analisis Bisnis</h1>
                <p class="text-xs text-gray-500">Visualisasi tren pendapatan, performa console, dan stok FnB</p>
            </div>
            <a href="{{ route('reports.transactions') }}" class="text-xs bg-indigo-50 text-indigo-600 font-semibold px-3 py-1.5 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition">
                ← Lihat Laporan Transaksi
            </a>
        </div>

        {{-- 1. Kartu Ringkasan (KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Sesi Aktif Saat Ini</span>
                <p class="text-2xl font-bold text-amber-500 mt-1">{{ $activeSessionsCount }} <span class="text-xs font-normal text-gray-500">Unit</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Sesi Selesai Hari Ini</span>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $completedTodayCount }} <span class="text-xs font-normal text-gray-500">Sesi</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Pendapatan Hari Ini</span>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Pendapatan Bulan Ini</span>
                <p class="text-xl font-bold text-indigo-600 mt-1">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- 2. Grafik Pendapatan & Console Populer --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Grafik 7 Hari --}}
            <div class="lg:col-span-2 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-sm font-bold text-gray-700 mb-4">Tren Pendapatan (7 Hari Terakhir)</h2>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Console Populer --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-sm font-bold text-gray-700 mb-3">Unit Console Terpopuler</h2>
                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($popularConsoles as $console)
                        <div class="py-2.5 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $console->name }}</p>
                                <p class="text-[10px] text-gray-400">Tipe: {{ $console->type ?? 'PS' }}</p>
                            </div>
                            <span class="bg-indigo-50 text-indigo-700 font-bold px-2.5 py-1 rounded-full text-[10px]">
                                {{ $console->rental_sessions_count }} Sesi
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 italic py-2">Belum ada data sesi.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- WIDGET TREN FNB / PRODUK TERLARIS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">🍿 Tren Penjualan FnB & Produk Terlaris</h3>
                    <p class="text-xs text-gray-500">Produk yang paling banyak dipesan oleh pelanggan</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    Lihat Semua Produk &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 uppercase font-semibold border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4 text-center">Total Terjual</th>
                            <th class="py-3 px-4 text-right">Total Pendapatan FnB</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($popularProducts as $index => $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-gray-800">
                                {{ $item->product->name ?? 'Produk Dihapus' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="bg-indigo-50 text-indigo-700 font-bold px-2.5 py-1 rounded-full border border-indigo-100">
                                    {{ $item->total_qty }} psc
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-gray-400 italic">
                                Belum ada data penjualan FnB.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        

    </div>

    {{-- Script Render Chart.js --}}
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: @json($chartData),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            },
                            font: { size: 10 }
                        }
                    },
                    x: {
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    </script>
</body>
</html>