<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Billing PS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

@include('layouts.navigation')

    <div class="max-w-7xl mx-auto px-2 pt-4">

        @if(!$activeShift)
        {{-- MODAL BUKA SHIFT (Jika belum Buka Shift) --}}
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="font-bold text-amber-800 text-sm">Shift Belum Dibuka</h4>
                    <p class="text-xs text-amber-600">Buka shift terlebih dahulu untuk dapat memulai transaksi rental.</p>
                </div>
            </div>
            
            <form action="{{ route('shift.start') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="number" name="starting_cash" placeholder="Kas Awal (Rp)" required class="text-xs border-amber-300 rounded-lg p-2 w-36">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-lg">
                    Mulai Shift
                </button>
            </form>
        </div>
    @else
        {{-- BAR INDIKATOR SHIFT AKTIF --}}
        <div class="bg-indigo-50 border border-indigo-200 p-3 rounded-xl mb-6 flex justify-between items-center text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-indigo-900">Shift Aktif Sejak: {{ \Carbon\Carbon::parse($activeShift->start_time)->format('H:i') }}</span>
                <span class="text-indigo-400">|</span>
                <span class="text-indigo-700">Modal Awal: <strong>Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong></span>
            </div>

            {{-- Form Tutup Shift --}}
            <form action="{{ route('shift.stop', $activeShift->id) }}" method="POST" onsubmit="return confirm('Tutup shift sekarang?')" class="flex gap-2 items-center">
                @csrf
                <input type="number" name="actual_cash" placeholder="Uang Tunai di Laci (Rp)" required class="text-xs border-indigo-300 rounded-lg p-1.5 w-44">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded-lg">
                    Tutup Shift
                </button>
            </form>
        </div>
    @endif

        {{-- Flash Message Notifikasi --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- GRID CARDS PER-CONSOLE --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($consoles as $console)
                @php
                    $activeSession = $console->sessions->first();
                @endphp

                {{-- WADAH KARTU CONSOLE --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden border-2 flex flex-col h-[520px] {{ $console->status === 'maintenance' ? 'border-amber-400 bg-amber-50/20' : ($activeSession ? 'border-red-500' : 'border-gray-200') }}">

            {{-- HEADER KARTU CONSOLE --}}
            <div class="p-4 flex-none flex justify-between items-center {{ $console->status === 'maintenance' ? 'bg-amber-500 text-white' : ($activeSession ? 'bg-red-500 text-white' : 'bg-green-600 text-white') }}">
                <div>
                    <h2 class="font-bold text-lg leading-tight">{{ $console->name }}</h2>
                    <p class="text-xs opacity-90">Rp {{ number_format($console->hourly_rate, 0, ',', '.') }}/jam</p>
                </div>
                <span class="text-xs font-extrabold px-2 py-1 rounded bg-white/20 uppercase tracking-wider">
                    @if($console->status === 'maintenance')
                        MAINTENANCE
                    @else
                        {{ $activeSession ? 'DIPAKAI' : 'KOSONG' }}
                    @endif
                </span>
            </div>

                    {{-- BODY KARTU (Mengisi ruang sisa & mengatur tata letak internal) --}}
                    <div class="p-4 flex-1 flex flex-col justify-between overflow-hidden">
                        {{-- 🔴 KONDISI 1: UNIT MAINTENANCE --}}
                        @if($console->status === 'maintenance')
                            <div class="flex-1 flex flex-col items-center justify-center text-center p-4">
                                <div class="my-auto py-6 px-4 border-2 border-dashed border-red-200 rounded-2xl bg-red-50/50 w-full">
                                    <span class="text-4xl block mb-2">🛠️</span>
                                    <p class="text-xs font-bold text-red-600 uppercase tracking-wide">Unit Nonaktif</p>
                                    <p class="text-[11px] text-gray-500 mt-1">Sedang dalam perbaikan / perawatan</p>
                                </div>
                            </div>
                            <button disabled type="button" class="w-full bg-gray-200 text-gray-400 font-bold py-2.5 rounded-xl text-xs cursor-not-allowed shadow-none">
                                🚫 Maintenance
                            </button>
                        @elseif(!$activeSession)
                            {{-- FORM MULAI RENTAL --}}
                            <form action="{{ route('rental.start') }}" method="POST" class="flex-1 flex flex-col justify-between">
                                @csrf
                                <input type="hidden" name="console_id" value="{{ $console->id }}">

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Mode Main</label>
                                        <select name="type" id="type_select_{{ $console->id }}" 
                                                onchange="togglePackageDropdown('{{ $console->id }}')" 
                                                class="w-full text-sm border rounded-lg p-2 focus:ring focus:ring-indigo-200" required>
                                            <option value="open">⏱️ Open Play (Per 15 Menit)</option>
                                            <option value="package">📦 Mode Paket</option>
                                        </select>
                                    </div>

                                    {{-- Dropdown Paket --}}
                                    <div id="package_container_{{ $console->id }}" class="hidden">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Paket</label>
                                        <select name="package_id" class="w-full text-sm border rounded-lg p-2 focus:ring focus:ring-indigo-200">
                                            @foreach($packages as $package)
                                                <option value="{{ $package->id }}">
                                                    {{ $package->name }} ({{ $package->duration_minutes }} mnt) - Rp {{ number_format($package->price, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Pilihan Promo Bonus Waktu (HANYA BONUS_TIME) --}}
                                <div class="mt-3.5 mb-3">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Promo Bonus Jam (Opsional):</label>
                                    <select name="promotion_id" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        <option value="">-- Tanpa Promo --</option>
                                        @foreach($promotions->where('type', 'bonus_time') as $promo)
                                            <option value="{{ $promo->id }}">
                                                🎉 {{ $promo->name }} (+{{ $promo->bonus_minutes }} Mnt Gratis)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Kotak Catatan Kasir --}}
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Kasir (Opsional)</label>
                                    <input type="text" name="notes" placeholder="Misal: Sudah bayar 15rb" 
                                        class="w-full text-xs border border-gray-300 rounded-lg p-2 bg-slate-50 focus:bg-white focus:outline-none">
                                </div>

                                {{-- 🟢 EMOJI PLACEHOLDER (Mengisi sisa ruang secara fleksibel & terpusat secara vertikal) --}}
                                <div class="flex-1 my-2 flex flex-col items-center justify-center py-4 text-center border-2 border-dashed border-green-200/80 rounded-xl bg-green-50/30">
                                    <div class="text-4xl mb-1.5 transition-transform hover:scale-110 duration-200">
                                        🎮
                                    </div>
                                    <span class="text-xs font-bold text-green-700 tracking-wide uppercase">Unit Ready to Play</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Pilih mode main lalu klik Mulai Rental</p>
                                </div>

                                {{-- Tombol Mulai (Otomatis Terdorong ke Bawah) --}}
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 rounded-lg transition mt-auto shadow">
                                    ▶️ Mulai Rental
                                </button>

                            </form>

                        @else
                            {{-- RINCIAN CONSOLE AKTIF & TIMER --}}
                            <div class="flex-1 flex flex-col justify-between overflow-y-auto pr-0.5 space-y-3">
                                <div class="text-sm space-y-2 flex-none">
                                    <div class="flex justify-between text-xs text-gray-600 border-b pb-1">
                                        <span>Mulai: <strong class="text-gray-800">{{ \Carbon\Carbon::parse($activeSession->start_time)->timezone('Asia/Jakarta')->format('H:i:s') }}</strong></span>
                                        <span class="font-bold text-indigo-700 uppercase">
                                            {{ $activeSession->type === 'package' ? ($activeSession->package->name ?? 'Paket') : 'Open Play' }}
                                        </span>
                                    </div>

                                    {{-- DISPLAY TIMER REAL-TIME --}}
                                    <div class="bg-gray-900 text-green-400 p-2.5 rounded-lg text-center font-mono border border-gray-700">
                                        <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Durasi Berjalan</p>
                                        <div class="text-2xl font-bold tracking-wider text-green-400 timer-display"
                                            data-start="{{ \Carbon\Carbon::parse($activeSession->start_time)->toIso8601String() }}"
                                            data-status="{{ $activeSession->status }}"
                                            data-duration="{{ $activeSession->type === 'package' && $activeSession->package ? $activeSession->package->duration_minutes : '' }}"
                                            data-extended="{{ $activeSession->extended_minutes ?? 0 }}">
                                            00:00:00
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1 extra-info"></p>
                                    </div>
                                </div>
                                

                                {{-- DAFTAR ORDER FNB --}}
                                <div class="flex-1 min-h-[90px]">
                                    <span class="text-xs font-semibold text-gray-500">Pesanan FnB:</span>
                                    @if($activeSession->orders && $activeSession->orders->count() > 0)
                                        {{-- 🟢 Diberikan max-h-[85px] & scrollbar agar tidak merusak tinggi kartu --}}
                                        <ul class="mt-1 space-y-1 max-h-[85px] overflow-y-auto pr-1">
                                            @foreach($activeSession->orders as $order)
                                                <li class="flex justify-between items-center text-xs bg-white p-1.5 rounded border border-gray-200 shadow-2xs">
                                                    <div class="truncate max-w-[130px]">
                                                        <span class="font-medium text-gray-800">{{ $order->product->name }}</span>
                                                        <span class="text-gray-500 font-bold">({{ $order->quantity }}x)</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 flex-none">
                                                        <span class="font-semibold text-gray-700">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                                        
                                                        <form action="{{ route('rental.order.delete', $order->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan {{ $order->product->name }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm px-0.5" title="Batal/Hapus Pesanan">
                                                                &times;
                                                            </button>
                                                        </form>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs text-gray-400 italic mt-1">Belum ada pesanan</p>
                                    @endif
                                </div>
                                

                                {{-- AREA AKSI BOTTOM (FORM PERPANJANG, TAMBAH FNB & STOP) --}}
                                <div class="flex-none pt-2 border-t space-y-2 mt-auto">
                                    {{-- FORM TAMBAH DURASI / PAKET --}}
                                    @if($activeSession->type === 'package')
                                    <form action="{{ route('rental.extend', $activeSession->id) }}" method="POST">
                                        @csrf
                                        <div class="flex items-center gap-1.5">
                                            <select name="package_id" class="text-xs border border-gray-300 rounded-lg p-1 flex-1 bg-white min-w-0" required>
                                                <option value="">-- Tambah Paket --</option>
                                                @foreach($packages as $pkg)
                                                    <option value="{{ $pkg->id }}">
                                                        + {{ $pkg->name }} ({{ $pkg->duration_minutes }}m) - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            
                                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs px-2 py-1 rounded-lg shadow-2xs whitespace-nowrap transition flex-shrink-0">
                                                + Tambah
                                            </button>
                                        </div>
                                    </form>
                                    @endif

                                    {{-- FORM TAMBAH FNB --}}
                                    <form action="{{ route('rental.order', $activeSession->id) }}" method="POST" class="flex gap-1.5">
                                        @csrf
                                        <select name="product_id" class="text-xs border border-gray-300 rounded-lg p-1 flex-1 bg-white min-w-0" required>
                                            <option value="">-- Pilih FnB --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                    {{ $product->name }} (Stok: {{ $product->stock }}) - Rp {{ number_format($product->price, 0, ',', '.') }}
                                                    {{ $product->stock <= 0 ? ' [HABIS]' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="quantity" value="1" min="1" class="text-xs border border-gray-300 rounded-lg w-10 p-1 text-center" required>
                                        <button type="submit" class="bg-blue-600 text-white text-xs px-2 py-1 rounded-lg hover:bg-blue-700 font-bold flex-none">
                                            +
                                        </button>
                                    </form>

                                    {{-- HITUNG ESTIMASI TAGIHAN SEMENTARA --}}
                                    @php
                                        $startTime = \Carbon\Carbon::parse($activeSession->start_time);
                                        $now = \Carbon\Carbon::now();
                                        $totalMinutes = $startTime->diffInMinutes($now);

                                        $estimatedRentalCost = 0;
                                        if ($activeSession->type === 'package' && $activeSession->package) {
                                            $estimatedRentalCost = $activeSession->package->price + $activeSession->extended_cost;
                                            $allowedDuration = $activeSession->package->duration_minutes + $activeSession->extended_minutes;
                                            
                                            if ($totalMinutes > $allowedDuration) {
                                                $extraMinutes = $totalMinutes - $allowedDuration;
                                                $extraBlocks = floor($extraMinutes / 15);
                                                $ratePerBlock = $activeSession->console->hourly_rate / 4;
                                                $estimatedRentalCost += ($extraBlocks * $ratePerBlock);
                                            }
                                        } else {
                                            $billableBlocks = floor($totalMinutes / 15);
                                            $ratePerBlock = $activeSession->console->hourly_rate / 4;
                                            $estimatedRentalCost = $billableBlocks * $ratePerBlock;
                                        }

                                        $fnbCost = $activeSession->orders->sum('subtotal');
                                        $grandTotal = $estimatedRentalCost + $fnbCost;
                                    @endphp

                                     <div class="grid grid-cols-2 gap-2 mb-2">
                                        {{-- Tombol Pindah Unit (Bukan Submit) --}}
                                        <button type="button" onclick="openTransferModal({{ $activeSession->id }}, '{{ $console->name }}')" 
                                            class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-2 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                            <span>🔄 Pindah Unit</span>
                                        </button>

                                        {{-- Form Pause / Resume Tersendiri --}}
                                        @if($activeSession->status === 'paused')
                                            <form action="{{ route('rental.resume', $activeSession->id) }}" method="POST" class="w-full m-0">
                                                @csrf
                                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-2 rounded-xl text-xs transition">
                                                    ▶ Resume
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('rental.pause', $activeSession->id) }}" method="POST" class="w-full m-0">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Pause sesi ini?')" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2.5 px-2 rounded-xl text-xs transition">
                                                    ⏸️ Pause
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    {{-- FORM STOP RENTAL --}}
                                    <form action="{{ route('rental.stop', $activeSession->id) }}" method="POST" class="space-y-2 mt-auto">
                                        @csrf

                                        {{-- 🟢 KOTAK CATATAN KASIR (DIPASANG DI SINI) --}}
                                        <div class="bg-amber-50 border border-amber-300 p-2 rounded-lg text-xs space-y-1.5 my-1">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold text-amber-900 flex items-center gap-1">
                                                    <span>📝</span> Catatan Kasir:
                                                </span>
                                            </div>
                                            
                                            <div class="flex gap-1">
                                                <input type="text" name="notes" value="{{ $activeSession->notes }}" placeholder="Tulis catatan / sisa bayar..." 
                                                    class="w-full text-xs border border-amber-300 rounded px-2 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                                                
                                                <button type="submit" formnovalidate formaction="{{ route('rental.update-notes', $activeSession->id) }}" 
                                                        class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-2.5 py-1 rounded transition text-[10px]">
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 🟢 TOTAL TAGIHAN BERWARNA & BORDER --}}
                                        <div class="flex justify-between items-center px-3 py-1.5 bg-slate-100 border border-slate-300 rounded-lg shadow-2xs my-1.5">
                                            <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider">Total Tagihan</span>
                                            <span class="text-sm font-black text-slate-900">
                                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        {{-- PILIHAN METODE PEMBAYARAN --}}
                                        <div class="space-y-1.5" x-data="{ method: 'cash', total: {{ $grandTotal }} }">
                                            <div class="flex items-center gap-2">
                                                <label class="text-[11px] font-medium text-gray-600 flex-none">Metode:</label>
                                                <select name="payment_method" 
                                                        x-model="method" 
                                                        onchange="document.getElementById('split-input-{{ $activeSession->id }}').style.display = (this.value === 'split') ? 'grid' : 'none'" 
                                                        required 
                                                        class="w-full text-xs border-gray-300 rounded-lg py-1 px-1.5">
                                                    <option value="cash">Tunai (Full Cash)</option>
                                                    <option value="qris">QRIS / Transfer (Full QRIS)</option>
                                                    <option value="split">🔀 Split (Cash + QRIS)</option>
                                                </select>
                                            </div>

                                            {{-- INPUT SPLIT PAYMENT DENGAN KALKULATOR OTOMATIS --}}
                                            <div id="split-input-{{ $activeSession->id }}" style="display: none;" class="grid-cols-2 gap-1.5 pt-1">
                                                <div>
                                                    <label class="text-[10px] text-gray-500 font-bold">Bayar Cash (Rp)</label>
                                                    <input type="number" 
                                                        name="cash_amount" 
                                                        placeholder="0" 
                                                        @input="$refs.qrisInput.value = Math.max(0, total - $el.value)"
                                                        class="w-full text-xs border-gray-300 rounded-lg p-1">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] text-gray-500 font-bold">Bayar QRIS (Rp)</label>
                                                    <input type="number" 
                                                        x-ref="qrisInput"
                                                        name="qris_amount" 
                                                        placeholder="0" 
                                                        class="w-full text-xs border-gray-300 rounded-lg p-1">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Pilihan Promo Diskon/Potongan --}}
                                        <div class="mb-4">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Promo / Diskon (Opsional):</label>
                                            <select name="promotion_id" id="stopPromotionSelect" onchange="calculateDiscount()" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                                <option value="" data-type="none" data-value="0" data-min-duration="0" data-min-amount="0">-- Tanpa Promo --</option>
                                                @foreach($promotions->whereIn('type', ['discount_nominal', 'discount_percent']) as $promo)
                                                    <option value="{{ $promo->id }}" 
                                                            data-type="{{ $promo->type }}" 
                                                            data-value="{{ $promo->discount_value }}" 
                                                            data-min-duration="{{ $promo->min_duration_minutes }}"
                                                            data-min-amount="{{ $promo->min_transaction_amount }}">
                                                        🎉 {{ $promo->name }} 
                                                        @if($promo->type === 'discount_nominal')
                                                            (Potongan Rp {{ number_format($promo->discount_value, 0, ',', '.') }})
                                                        @elseif($promo->type === 'discount_percent')
                                                            (Diskon {{ $promo->discount_value }}%)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p id="promoWarning" class="text-[11px] text-red-500 font-semibold mt-1 hidden"></p>
                                        </div>

                                        <script>
                                            function calculateDiscount() {
                                            const select = document.getElementById('stopPromotionSelect');
                                            const selectedOption = select.options[select.selectedIndex];
                                            const warningEl = document.getElementById('promoWarning');
                                            
                                            if (!selectedOption || select.value === "") {
                                                if (warningEl) warningEl.classList.add('hidden');
                                                return;
                                            }

                                            // Ambil data atribut dari opsi promo yang dipilih
                                            const minDuration = parseInt(selectedOption.getAttribute('data-min-duration')) || 0;
                                            const minAmount = parseFloat(selectedOption.getAttribute('data-min-amount')) || 0;

                                            // Ambil durasi & total tagihan sesi saat ini (sesuaikan dengan ID elemen di modal kamu)
                                            const currentDuration = parseInt(document.getElementById('sessionDurationMinutes')?.value || 0);
                                            const currentTotal = parseFloat(document.getElementById('sessionTotalAmount')?.value || 0);

                                            let isEligible = true;
                                            let errorMessage = "";

                                            // Validasi syarat durasi
                                            if (minDuration > 0 && currentDuration < minDuration) {
                                                isEligible = false;
                                                errorMessage = `Syarat promo ini min. durasi sewa ${minDuration} menit.`;
                                            }

                                            // Validasi syarat nominal transaksi
                                            if (minAmount > 0 && currentTotal < minAmount) {
                                                isEligible = false;
                                                errorMessage = `Syarat promo ini min. transaksi Rp ${minAmount.toLocaleString('id-ID')}.`;
                                            }

                                            if (!isEligible) {
                                                if (warningEl) {
                                                    warningEl.innerText = `⚠️ ${errorMessage}`;
                                                    warningEl.classList.remove('hidden');
                                                }
                                            } else {
                                                if (warningEl) warningEl.classList.add('hidden');
                                            }
                                        }</script>

                                       
                                        {{-- 🔒 TOMBOL STOP & STRUK HANYA AKTIF JIKA SHIFT SUDAH DIBUKA --}}
                                        @php
                                            $hasOpenShift = \App\Models\Shift::where('user_id', auth()->id())
                                                ->where('status', 'open')
                                                ->exists();
                                        @endphp

                                        @if(!$hasOpenShift)
                                            {{-- Tombol mati jika Shift belum dibuka --}}
                                            <button type="button" 
                                                    onclick="alert('Buka Shift terlebih dahulu sebelum menyelesaikan sesi rental!')" 
                                                    class="w-full bg-gray-400 text-white py-2 rounded-xl font-bold text-xs cursor-not-allowed mt-2">
                                                🔒 Buka Shift Untuk Hentikan Sesi
                                            </button>
                                        @else
                                            {{-- Tombol normal jika Shift sudah dibuka --}}
                                            {{-- 1. Tombol Stop & Struk --}}
                                            <button type="submit" onclick="return confirm('Selesaikan sesi rental ini?')" 
                                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-2 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                                🛑 <span>Stop & Struk</span>
                                            </button>

                                        @endif
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

   {{-- JAVASCRIPT UNTUK DROPDOWN & TIMER LIVE --}}
<script>
function togglePackageDropdown(consoleId) {
    const modeSelect = document.getElementById('type_select_' + consoleId);
    const packageContainer = document.getElementById('package_container_' + consoleId);

    if (modeSelect.value === 'package') {
        packageContainer.classList.remove('hidden');
    } else {
        packageContainer.classList.add('hidden');
    }
}

// 🔔 Fungsi Memutar Audio Alarm dari File Lokal (public/sounds/alarm.mp3)
function playAlarmSound() {
    // Memanggil file alarm.mp3 dari folder public/sounds/
    const alarmAudio = new Audio('/sounds/alarm.mp3');
    
    alarmAudio.volume = 1.0; // Volume maksimal (100%)
    
    alarmAudio.play().catch(error => {
        console.log('Autoplay audio diblokir oleh browser. Klik area mana saja di layar untuk mengaktifkan suara alarm.');
    });
}

function updateTimers() {
    const timers = document.querySelectorAll('.timer-display');

    timers.forEach(timer => {
        // ⏸️ CEK JIKA STATUS SESI ADALAH PAUSED
        if (timer.dataset.status === 'paused') {
            timer.textContent = "PAUSED";
            timer.className = 'text-2xl font-bold tracking-wider text-yellow-500 animate-pulse timer-display';
            
            const extraInfo = timer.nextElementSibling;
            if (extraInfo) {
                extraInfo.textContent = '⏸️ Sesi Sedang Dijeda';
                extraInfo.className = 'text-[10px] text-yellow-400 font-bold mt-1 extra-info';
            }
            return; // Lewati perhitungan waktu di bawahnya
        }
        const startTime = new Date(timer.dataset.start).getTime();
        // Ambil durasi paket dasar
        const baseDuration = timer.dataset.duration ? parseInt(timer.dataset.duration) : null;
        // Ambil durasi perpanjangan (extended_minutes)
        const extendedDuration = timer.dataset.extended ? parseInt(timer.dataset.extended) : 0;

        const now = new Date().getTime();

        const elapsedSeconds = Math.floor((now - startTime) / 1000);
        if (elapsedSeconds < 0) return;

        const extraInfo = timer.nextElementSibling;

        // ===========================================
        // KONDISI 1: MODE PAKET (COUNTDOWN MUNDUR)
        // ===========================================
        if (baseDuration !== null) {
            // TOTAL WAKTU PAKET = WAKTU AWAL + WAKTU PERPANJANGAN
            const totalPackageMinutes = baseDuration + extendedDuration;
            const totalPackageSeconds = totalPackageMinutes * 60;
            const remainingSeconds = totalPackageSeconds - elapsedSeconds;

            if (remainingSeconds >= 0) {
                // Masih ada sisa waktu paket (Hitung Mundur)
                const hours = String(Math.floor(remainingSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((remainingSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(Math.floor(remainingSeconds % 60)).padStart(2, '0');

                timer.textContent = `${hours}:${minutes}:${seconds}`;

                // --- INDIKATOR WARNA & ALARM ---
                if (remainingSeconds <= 300) {
                    // 🟠 SISA WAKTU <= 5 MENIT (Oranye Kedip + Alarm 1x)
                    timer.className = 'text-2xl font-bold tracking-wider text-orange-500 animate-pulse timer-display';
                    if (extraInfo) {
                        extraInfo.textContent = extendedDuration > 0 ? `⏳ Sisa Waktu (+${extendedDuration} mnt)` : '⏳ Sisa Waktu (< 5 Mnt)';
                        extraInfo.className = 'text-[10px] text-orange-400 font-bold mt-1 extra-info';
                    }

                    // Bunyi alarm jika baru pertama kali menyentuh 5 menit terakhir
                    if (timer.dataset.alerted5min !== 'true') {
                        playAlarmSound();
                        timer.dataset.alerted5min = 'true';
                    }

                } else if (remainingSeconds <= 900) {
                    // 🟡 SISA WAKTU <= 15 MENIT (Kuning)
                    timer.className = 'text-2xl font-bold tracking-wider text-yellow-500 timer-display';
                    if (extraInfo) {
                        extraInfo.textContent = extendedDuration > 0 ? `⏳ Sisa Waktu (+${extendedDuration} mnt)` : '⏳ Sisa Waktu (< 15 Mnt)';
                        extraInfo.className = 'text-[10px] text-yellow-400 font-bold mt-1 extra-info';
                    }

                } else {
                    // 🟢 NORMAL (> 15 MENIT)
                    timer.className = 'text-2xl font-bold tracking-wider text-green-400 timer-display';
                    if (extraInfo) {
                        extraInfo.textContent = extendedDuration > 0 ? `⏳ Sisa Waktu (+${extendedDuration} mnt)` : '⏳ Sisa Waktu';
                        extraInfo.className = 'text-[10px] text-green-300 mt-1 extra-info';
                    }
                }

            } else {
                // 🔴 WAKTU PAKET HABIS -> OVERTIME
                const overSeconds = Math.abs(remainingSeconds);
                const hours = String(Math.floor(overSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((overSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(Math.floor(overSeconds % 60)).padStart(2, '0');

                timer.textContent = `+${hours}:${minutes}:${seconds}`;
                timer.className = 'text-2xl font-bold tracking-wider text-red-500 animate-pulse timer-display';

                if (extraInfo) {
                    extraInfo.textContent = '⚠️️ WAKTU PAKET HABIS (OVERTIME)';
                    extraInfo.className = 'text-[10px] text-red-400 font-bold mt-1 extra-info';
                }

                // Bunyi alarm saat waktu tepat habis
                if (timer.dataset.alertedfinished !== 'true') {
                    playAlarmSound();
                    timer.dataset.alertedfinished = 'true';
                }
            }

        } 
        // ===========================================
        // KONDISI 2: OPEN PLAY (COUNT UP MAJU)
        // ===========================================
        else {
            const hours = String(Math.floor(elapsedSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((elapsedSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(Math.floor(elapsedSeconds % 60)).padStart(2, '0');

            timer.textContent = `${hours}:${minutes}:${seconds}`;
            timer.className = 'text-2xl font-bold tracking-wider text-green-400 timer-display';

            if (extraInfo) {
                extraInfo.textContent = '⏱️ Open Play';
                extraInfo.className = 'text-[10px] text-gray-400 mt-1 extra-info';
            }
        }
    });
}

setInterval(updateTimers, 1000);
updateTimers();
</script>
<!-- Modal Pop-Up Struk (Tailwind CSS) -->
@if(session('show_receipt_id'))
    @php
        $receiptSession = \App\Models\RentalSession::with(['console', 'package', 'orders.product'])->find(session('show_receipt_id'));
    @endphp

    @if($receiptSession)
    <!-- Overlay Background Dark -->
    <div id="receiptModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4">
        
        <!-- Card Pop-Up Modal -->
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm overflow-hidden font-mono text-sm text-gray-800">
            
            <!-- Header Modal -->
            <div class="bg-gray-900 text-white px-4 py-3 flex justify-between items-center">
                <h3 class="font-bold text-base">Rincian Pembayaran</h3>
                <button type="button" onclick="closeReceiptModal()" class="text-gray-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <!-- Body Struk (Print Area) -->
            <div class="p-4 bg-white" id="printableReceipt">
                <div class="text-center mb-3">
                    <h2 class="font-bold text-lg uppercase">RENTAL PS KITA</h2>
                    <p class="text-xs text-gray-500">Jl. Contoh No. 123, Kota</p>
                </div>
                
                <div class="border-b border-dashed border-gray-400 my-2"></div>

                <div class="text-xs space-y-1">
                    <div><span class="inline-block w-20">No. Nota</span>: #{{ $receiptSession->id }}</div>
                    <div><span class="inline-block w-20">Mulai</span>: {{ \Carbon\Carbon::parse($receiptSession->start_time)->format('d/m/Y H:i') }}</div>
                    <div><span class="inline-block w-20">Selesai</span>: {{ \Carbon\Carbon::parse($receiptSession->end_time)->format('d/m/Y H:i') }}</div>
                    <div><span class="inline-block w-20">Console</span>: {{ $receiptSession->console->name }}</div>
                </div>

                <div class="border-b border-dashed border-gray-400 my-2"></div>

                <!-- Rincian Item -->
                <table class="w-full text-xs">
                    <tr>
                        <td colspan="2" class="font-semibold">Sewa {{ $receiptSession->console->name }}</td>
                    </tr>

                    {{-- 1. Paket Utama / Open Play --}}
                    <tr>
                        <td>
                            @if($receiptSession->type == 'package')
                                {{ $receiptSession->package->name ?? 'Paket' }} ({{ $receiptSession->package->duration_minutes ?? 0 }} mnt)
                            @else
                                Open Play
                            @endif
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($receiptSession->type == 'package' ? ($receiptSession->package->price ?? 0) : $receiptSession->rental_cost, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- 2. Paket Perpanjangan (Jika Ada) --}}
                    @if($receiptSession->extended_minutes > 0)
                    <tr>
                        <td class="pl-2 italic text-gray-600">
                            + Ext: {{ $receiptSession->extended_package_name ?? 'Tambahan Paket' }} ({{ $receiptSession->extended_minutes }} mnt)
                        </td>
                        <td class="text-right italic text-gray-600">
                            Rp {{ number_format($receiptSession->extended_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif

                    {{-- 3. Rincian Order FnB --}}
                    @if($receiptSession->orders && $receiptSession->orders->count() > 0)
                        @foreach($receiptSession->orders as $order)
                        <tr>
                            <td colspan="2" class="pt-2 font-semibold">{{ $order->product->name }}</td>
                        </tr>
                        <tr>
                            <td>{{ $order->quantity }} x {{ number_format($order->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @endif
                </table>

                <div class="border-b border-dashed border-gray-400 my-2"></div>

                <!-- Total -->
                <table class="w-full text-xs">
                    <tr>
                        <td>Biaya Rental</td>
                        <td class="text-right">Rp {{ number_format($receiptSession->rental_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($receiptSession->fnb_cost > 0)
                    <tr>
                        <td>Total FnB</td>
                        <td class="text-right">Rp {{ number_format($receiptSession->fnb_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="font-bold text-sm pt-1">
                        <td>TOTAL BIAYA</td>
                        <td class="text-right">Rp {{ number_format($receiptSession->total_cost, 0, ',', '.') }}</td>
                    </tr>
                    {{-- TAMBAHKAN BARIS METODE PEMBAYARAN DI SINI --}}
                <tr class="border-t border-dashed border-gray-400">
                    <td class="pt-1">Metode Bayar</td>
                    <td class="text-right pt-1 font-bold uppercase">
                        @if(($receiptSession->payment_method ?? 'cash') === 'qris')
                            QRIS / Transfer
                        @elseif(($receiptSession->payment_method ?? 'cash') === 'split')
                            Split Payment
                        @else
                            Cash / Tunai
                        @endif
                    </td>
                </tr>
                </table>

                <div class="border-b border-dashed border-gray-400 my-2"></div>

                <div class="text-center text-xs text-gray-500 mt-2">
                    <p>-- Terima Kasih --</p>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="bg-gray-100 px-4 py-3 flex justify-between gap-2 border-t border-gray-200">
                <button type="button" onclick="closeReceiptModal()" class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white rounded text-xs font-semibold">
                    Tutup (Tanpa Cetak)
                </button>
                <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">
                    Cetak Struk
                </button>
            </div>

        </div>
    </div>
    <script>
        function closeReceiptModal() {
            document.getElementById('receiptModal').remove();
        }
    </script>
    @endif
@endif

{{-- MODAL PINDAH KONSOL --}}
<div id="transferModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
        <h3 class="text-base font-bold text-gray-800 mb-1">Pindah Konsol / Unit</h3>
        <p class="text-xs text-gray-500 mb-4">Pindahkan sesi berjalan dari <span id="currentConsoleName" class="font-bold text-indigo-600"></span> ke unit lain.</p>

        <form id="transferForm" method="POST" action="">
            @csrf
            
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Konsol Tujuan (Tersedia):</label>
                <select name="new_console_id" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 bg-white text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm">
                    <option value="">-- Pilih Unit Kosong --</option>
                    
                    @forelse($availableConsoles as $console)
                        <option value="{{ $console->id }}">
                            {{ $console->name }} (Rp {{ number_format($console->hourly_rate ?? 0, 0, ',', '.') }}/jam)
                        </option>
                    @empty
                        <option value="" disabled class="text-gray-400">⚠️ Tidak ada unit lain yang sedang kosong</option>
                    @endforelse
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeTransferModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    Konfirmasi Pindah
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function openTransferModal(sessionId, consoleName) {
    const modal = document.getElementById('transferModal');
    const form = document.getElementById('transferForm');
    const consoleNameDisplay = document.getElementById('currentConsoleName');

    // Set action URL form secara dinamis
    form.action = `/rental-sessions/${sessionId}/transfer`;
    consoleNameDisplay.innerText = consoleName;

    modal.classList.remove('hidden');
}

function closeTransferModal() {
    const modal = document.getElementById('transferModal');
    modal.classList.add('hidden');
}
</script>
</body>
</html>