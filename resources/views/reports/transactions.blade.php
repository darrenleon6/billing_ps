<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Rental PS Kita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    @include('layouts.navigation')

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Header Halaman --}}
        <div>
            <h1 class="text-xl font-bold text-gray-800">Laporan Transaksi & Keuangan</h1>
            <p class="text-xs text-gray-500">
                @if(auth()->user()->role === 'admin')
                    Rekap riwayat penyewaan PS dan penjualan FnB
                @else
                    Rekap transaksi harian untuk sesi kasir/operator
                @endif
            </p>
        </div>

        {{-- Container Alert Error Frontend --}}
        <div id="frontendAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold">
            <span id="alertMessage"></span>
        </div>

        {{-- CONTAINER RINGKASAN LAPORAN --}}
        <div class="space-y-4 mb-6">
            
            {{-- BARIS 1: PENDAPATAN UTAMA (HIGHLIGHT) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Total Pendapatan Keseluruhan --}}
                <div class="bg-gradient-to-r from-slate-900 to-indigo-900 p-5 rounded-2xl shadow-sm text-white flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Total Pendapatan Keseluruhan</p>
                        <h3 class="text-2xl font-black text-white mt-1">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-indigo-300 mt-1">Sewa PS + Omset FnB Kotor</p>
                    </div>
                    <div class="w-14 h-14 flex-none bg-white/10 rounded-xl border border-white/10 flex flex-col items-center justify-center text-3xl">
                        <span class="pb-1">💵</span>
                    </div>
                </div>

                {{-- Pendapatan Rental PS --}}
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pendapatan Rental PS</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalRental, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-gray-400 mt-1">Murni dari biaya sewa konsol</p>
                    </div>
                    <div class="w-14 h-14 flex-none bg-indigo-50 text-indigo-600 rounded-xl flex flex-col items-center justify-center text-3xl">
                        <span class="pb-1">🎮</span>
                    </div>
                </div>
            </div>

            {{-- BARIS 2: RINCIAN PERFORMA FNB --}}
            @if(auth()->user()->role === 'admin')
                {{-- TAMPILAN ADMIN (3 KARTU: OMSET, HPP, PROFIT) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Omset FnB Kotor --}}
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Omset FnB (Kotor)</p>
                            <h4 class="text-lg font-bold text-blue-600 mt-0.5">Rp {{ number_format($fnbRevenue, 0, ',', '.') }}</h4>
                        </div>
                        <div class="w-10 h-10 flex-none bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                            🍿
                        </div>
                    </div>

                    {{-- Modal FnB (HPP) --}}
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Modal FnB (HPP)</p>
                            <h4 class="text-lg font-bold text-amber-600 mt-0.5">Rp {{ number_format($fnbHPP, 0, ',', '.') }}</h4>
                        </div>
                        <div class="w-10 h-10 flex-none bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-2xl">
                            📦
                        </div>
                    </div>

                    {{-- Laba Bersih FnB (Profit) --}}
                    <div class="bg-emerald-50/60 p-4 rounded-xl shadow-sm border border-emerald-200/80 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Laba Bersih FnB (Profit)</p>
                            <h4 class="text-lg font-extrabold text-emerald-600 mt-0.5">Rp {{ number_format($fnbProfit, 0, ',', '.') }}</h4>
                        </div>
                        <div class="w-10 h-10 flex-none bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center text-2xl">
                            💰
                        </div>
                    </div>
                </div>
            @else
                {{-- TAMPILAN OPERATOR (HANYA OMSET FNB KOTOR) --}}
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Omset FnB</p>
                        <h4 class="text-lg font-bold text-blue-600 mt-0.5">Rp {{ number_format($fnbRevenue, 0, ',', '.') }}</h4>
                        <p class="text-[10px] text-gray-400 mt-0.5">Total penjualan makanan/minuman hari ini</p>
                    </div>
                    <div class="w-10 h-10 flex-none bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                        🍿
                    </div>
                </div>
            @endif

        {{-- FORM FILTER TANGGAL (HANYA MUNCUL UNTUK ADMIN) --}}
        @if(auth()->user()->role === 'admin')
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <form action="{{ route('reports.transactions') }}" method="GET" onsubmit="return validateDateFilter(event)" class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="text-gray-600 font-semibold">Dari Tanggal:</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date', $startDate) }}" required
                               class="border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 text-xs">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-gray-600 font-semibold">Sampai Tanggal:</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', $endDate) }}" required
                               class="border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 text-xs">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                            Filter
                        </button>
                        @if(request('start_date') || request('end_date'))
                            <a href="{{ route('reports.transactions') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-2 rounded-lg transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        @else
            {{-- INFORMASI LAPORAN HARIAN UNTUK OPERATOR --}}
            <div class="bg-indigo-50 border border-indigo-200 text-indigo-900 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
                <span class="text-base">ℹ️</span>
                <span>Menampilkan ringkasan transaksi <strong>Hari Ini ({{ \Carbon\Carbon::today()->format('d M Y') }})</strong>.</span>
            </div>
        @endif

        {{-- Tabel Riwayat Transaksi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 font-bold text-gray-700 bg-gray-50 flex justify-between items-center">
                <span>Riwayat Transaksi Selesai</span>
                <span class="text-xs font-normal text-gray-500">Total: {{ $sessions->count() }} Transaksi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase tracking-wider border-b">
                            <th class="p-3">Tanggal & Waktu</th>
                            <th class="p-3">Unit PS</th>
                            <th class="p-3">Sewa PS</th>
                            <th class="p-3">Item FnB</th>
                            <th class="p-3">Total Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($sessions as $session)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 text-gray-600">
                                {{ \Carbon\Carbon::parse($session->end_time)->format('d M Y, H:i') }}
                            </td>
                            <td class="p-3 font-bold text-gray-800">
                                {{ $session->console->name ?? 'Console' }}
                            </td>
                            <td class="p-3 text-gray-700">
                                Rp {{ number_format($session->rental_cost, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-gray-600">
                                @forelse($session->orders as $order)
                                    <div>• {{ $order->product->name ?? 'Produk' }} (x{{ $order->quantity }})</div>
                                @empty
                                    <span class="text-gray-400 italic">Tanpa FnB</span>
                                @endforelse
                            </td>
                            <td class="p-3 font-bold text-indigo-600">
                                Rp {{ number_format($session->total_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500 italic">
                                Belum ada riwayat transaksi yang sesuai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Script Validasi Frontend (Hanya aktif untuk Admin) --}}
    @if(auth()->user()->role === 'admin')
        <script>
            function validateDateFilter(event) {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const alertBox = document.getElementById('frontendAlert');
                const alertMsg = document.getElementById('alertMessage');

                alertBox.classList.add('hidden');

                if (!startDate || !endDate) {
                    event.preventDefault();
                    alertMsg.innerText = '⚠️ Kedua tanggal (Dari Tanggal & Sampai Tanggal) wajib diisi!';
                    alertBox.classList.remove('hidden');
                    return false;
                }

                if (startDate > endDate) {
                    event.preventDefault();
                    alertMsg.innerText = '⚠️ "Dari Tanggal" tidak boleh lebih besar dari "Sampai Tanggal"!';
                    alertBox.classList.remove('hidden');
                    return false;
                }

                return true;
            }
        </script>
    @endif

</body>
</html>