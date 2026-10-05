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
                            <th class="p-3">Jam Mulai dan Selesai</th>
                            <th class="p-3">Sewa PS</th>
                            <th class="p-3">Item FnB</th>
                            <th class="p-3">Metode</th>
                            <th class="px-4 py-3">Cash</th>
                            <th class="px-4 py-3">QRIS</th>
                            <th class="p-3">Total Tagihan</th>
                            
                            {{-- 🟢 Header untuk Kolom Aksi Khusus Admin --}}
                            @if(auth()->check() && auth()->user()->role === 'admin')
                                <th class="p-3 text-center">Aksi</th>
                            @endif
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
                            {{-- Kolom Jam Mulai & Selesai (BARU) --}}
                            <td class="p-3.5 px-4 font-mono font-semibold text-gray-900">
                                {{ $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-' }} 
                                <span class="text-gray-400 mx-1 font-normal">s/d</span> 
                                {{ $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '-' }}
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
                            <td class="p-3 font-bold text-gray-800">
                                {{ strtoupper($session->payment_method ?? 'Payment Method') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-green-600 font-semibold">
                                @if($session->cash_amount > 0)
                                    Rp {{ number_format($session->cash_amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Kolom QRIS -->
                            <td class="px-4 py-3 text-sm text-blue-600 font-semibold">
                                @if($session->qris_amount > 0)
                                    Rp {{ number_format($session->qris_amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3 font-bold text-indigo-600">
                                Rp {{ number_format($session->total_cost, 0, ',', '.') }}
                            </td>
                            {{-- 🟢 Tombol Edit & Hapus Khusus Admin --}}
                            @if(auth()->check() && auth()->user()->role === 'admin')
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Tombol Edit -->
                                       <button type="button" 
                                        onclick="openEditModal(
                                            '{{ $session->id }}', 
                                            '{{ addslashes($session->console->name ?? 'Console') }}', 
                                            '{{ $session->rental_cost }}', 
                                            {{ $session->orders->toJson() }}, 
                                            '{{ route('reports.transactions.update', $session->id) }}',
                                            '{{ $session->cash_amount ?? 0 }}', 
                                            '{{ $session->qris_amount ?? 0 }}',
                                            '{{ $session->payment_method ?? 'cash' }}'
                                        )" 
                                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm font-medium transition" 
                                        title="Edit Transaksi">
                                        Edit
                                    </button>

                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('reports.transactions.destroy', $session->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Data pendapatan akan disesuaikan kembali.');" 
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-2.5 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded shadow-sm font-medium transition" 
                                                    title="Hapus Transaksi">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                           {{-- colspan disesuaikan jika admin (6 kolom) atau operator (5 kolom) --}}
                            <td colspan="{{ (auth()->check() && auth()->user()->role === 'admin') ? 6 : 5 }}" class="p-4 text-center text-gray-500 italic">
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
    {{-- MODAL EDIT TRANSAKSI & FNB --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-bold text-gray-800">Edit Transaksi Unit: <span id="modalConsoleName" class="text-indigo-600"></span></h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                {{-- Biaya Sewa PS --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Biaya Sewa PS (Rp)</label>
                    <input type="number" name="rental_cost" id="modalRentalCost" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                </div>

               <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Metode Pembayaran</label>
                    <select name="payment_method" id="modalPaymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="split">Split Payment</option>
                    </select>
                </div>

                <!-- Input Nominal Cash -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nominal Cash (Rp)</label>
                        <!-- 🟢 Tambahkan id="cashAmountInput" dan hapus number_format dari value input angka -->
                        <input type="number" id="cashAmountInput" name="cash_amount" value="{{ $session->cash_amount ?? 0 }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm">
                    </div>

                    <!-- Input Nominal QRIS -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nominal QRIS (Rp)</label>
                        <!-- 🟢 Tambahkan id="qrisAmountInput" dan hapus number_format dari value input angka -->
                        <input type="number" id="qrisAmountInput" name="qris_amount" value="{{ $session->qris_amount ?? 0 }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm">
                    </div>
                {{-- Kelola Item FnB --}}
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-bold text-gray-700 uppercase">Item FnB (Opsional)</label>
                        <button type="button" onclick="addFnbRow()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            + Tambah Item
                        </button>
                    </div>
                    
                    <div id="fnbListContainer" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none">
                        {{-- Baris item FnB dirender dinamis via JS --}}
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // Simpan daftar semua produk dari database ke variabel global JS
    const allProducts = @json(\App\Models\Product::all());

    function openEditModal(id, consoleName, rentalCost, orders, updateUrl, cashAmount, qrisAmount, paymentMethod) {
        document.getElementById('modalConsoleName').innerText = consoleName;
        document.getElementById('modalRentalCost').value = rentalCost;
        document.getElementById('editForm').action = updateUrl;
        document.getElementById('cashAmountInput').value = cashAmount;
        document.getElementById('qrisAmountInput').value = qrisAmount;
        const paymentSelect = document.getElementById('modalPaymentMethod');
        if (paymentSelect) {
            paymentSelect.value = paymentMethod; 
        }

        // Kosongkan container FnB dulu
        const container = document.getElementById('fnbListContainer');
        container.innerHTML = '';

        // Jika ada order FnB sebelumnya, masukkan ke baris modal
        if (orders && orders.length > 0) {
            orders.forEach(order => {
                addFnbRow(order.product_id, order.quantity);
            });
        } else {
            // Biarkan kosong atau sediakan 1 baris kosong opsional
            addFnbRow();
        }

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function addFnbRow(selectedProductId = '', quantity = 1) {
        const container = document.getElementById('fnbListContainer');
        
        let optionsHtml = '<option value="">-- Pilih Menu FnB --</option>';
        allProducts.forEach(prod => {
            let selected = (prod.id == selectedProductId) ? 'selected' : '';
            optionsHtml += `<option value="${prod.id}" ${selected}>${prod.name} (Rp ${Number(prod.price).toLocaleString('id-ID')})</option>`;
        });

        const rowDiv = document.createElement('div');
        rowDiv.className = 'flex items-center gap-2 bg-gray-50 p-2.5 rounded-xl border border-gray-200';
        rowDiv.innerHTML = `
            <select name="products[]" class="w-3/4 px-3 py-1.5 border border-gray-300 bg-white rounded-lg shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500 outline-none">
                ${optionsHtml}
            </select>
            <input type="number" name="quantities[]" value="${quantity}" min="1" class="w-1/5 px-2 py-1.5 border border-gray-300 bg-white rounded-lg shadow-sm text-xs text-center focus:border-indigo-500 focus:ring-indigo-500 outline-none" placeholder="Qty">
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 font-bold px-1.5 py-1 text-sm transition" title="Hapus Item">&times;</button>
        `;
        container.appendChild(rowDiv);
    }
    </script>
</body>
</html>