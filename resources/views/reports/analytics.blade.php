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

        {{-- 3. Alert Stok FnB Menipis --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-amber-50 flex justify-between items-center">
                <span class="font-bold text-amber-800 text-xs flex items-center gap-1.5">
                    ⚠️ Peringatan Stok FnB Menipis (Stok ≤ 5)
                </span>
                <a href="{{ route('products.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">Kelola Stok →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase border-b">
                            <th class="p-3">Nama Produk</th>
                            <th class="p-3">Harga Jual</th>
                            <th class="p-3">Sisa Stok</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($lowStockProducts as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold text-gray-800">{{ $product->name }}</td>
                                <td class="p-3 text-gray-600">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="p-3 font-bold {{ $product->stock == 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $product->stock }} Pcs
                                </td>
                                <td class="p-3">
                                    @if($product->stock == 0)
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold">Habis</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[10px] font-bold">Hampir Habis</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400 italic">
                                    Semua stok produk FnB masih aman (di atas 5 Pcs).
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