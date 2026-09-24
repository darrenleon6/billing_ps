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
            <p class="text-xs text-gray-500">Rekap riwayat penyewaan PS dan penjualan FnB</p>
        </div>

        {{-- Container Alert Error Frontend --}}
        <div id="frontendAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl text-xs font-semibold">
            <span id="alertMessage"></span>
        </div>

        {{-- Ringkasan Pendapatan (3 Kartu) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Total Rental PS</span>
                <p class="text-xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalRental ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Total Penjualan FnB</span>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalFnB ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase">Total Pendapatan Keseluruhan</span>
                <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($grandTotal ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Form Filter Tanggal --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <form action="{{ route('reports.transactions') }}" method="GET" onsubmit="return validateDateFilter(event)" class="flex flex-wrap items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <label class="text-gray-600 font-semibold">Dari Tanggal:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" required
                           class="border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 text-xs">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-gray-600 font-semibold">Sampai Tanggal:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" required
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
                                Belum ada riwayat transaksi yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Script Validasi Frontend --}}
    <script>
        function validateDateFilter(event) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const alertBox = document.getElementById('frontendAlert');
            const alertMsg = document.getElementById('alertMessage');

            // Reset pesan error
            alertBox.classList.add('hidden');

            // 1. Cek apakah kedua tanggal sudah diisi
            if (!startDate || !endDate) {
                event.preventDefault();
                alertMsg.innerText = '⚠️ Kedua tanggal (Dari Tanggal & Sampai Tanggal) wajib diisi!';
                alertBox.classList.remove('hidden');
                return false;
            }

            // 2. Cek apakah start_date > end_date
            if (startDate > endDate) {
                event.preventDefault();
                alertMsg.innerText = '⚠️ "Dari Tanggal" tidak boleh lebih besar dari "Sampai Tanggal"!';
                alertBox.classList.remove('hidden');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>