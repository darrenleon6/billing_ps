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

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-indigo-700">🎮 Dashboard Billing Rental PS</h1>

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
                    $activeSession =$console->sessions->first();
                @endphp

                <div class="bg-white rounded-xl shadow-md overflow-hidden border-2 {{ $activeSession ? 'border-red-400 bg-red-50/20' : 'border-green-400 bg-green-50/20' }}">
                    
                    {{-- HEADER KARTU CONSOLE --}}
                    <div class="p-4 border-b flex justify-between items-center {{ $activeSession ? 'bg-red-500 text-white' : 'bg-green-600 text-white' }}">
                        <div>
                            <h2 class="font-bold text-lg leading-tight">{{ $console->name }}</h2>
                            <p class="text-xs opacity-90">Rp {{ number_format($console->hourly_rate, 0, ',', '.') }}/jam</p>
                        </div>
                        <span class="text-xs font-extrabold px-2 py-1 rounded bg-white/20 uppercase tracking-wider">
                            {{ $activeSession ? 'DIPAKAI' : 'KOSONG' }}
                        </span>
                    </div>

                    <div class="p-4 space-y-4">
                        @if(!$activeSession)
                            {{-- FORM MULAI RENTAL --}}
                            <form action="{{ route('rental.start') }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="console_id" value="{{ $console->id }}">

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Mode Main</label>
                                    <select name="type" id="type_select_{{ $console->id }}" 
                                            onchange="togglePackageDropdown('{{ $console->id }}')" 
                                            class="w-full text-sm border rounded p-2 focus:ring focus:ring-indigo-200" required>
                                        <option value="open">⏱️ Open Play (Per 15 Menit)</option>
                                        <option value="package">📦 Mode Paket</option>
                                    </select>
                                </div>

                                {{-- Dropdown Paket --}}
                                <div id="package_container_{{ $console->id }}" class="hidden">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Paket</label>
                                    <select name="package_id" class="w-full text-sm border rounded p-2 focus:ring focus:ring-indigo-200">
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}">
                                                {{ $package->name }} ({{ $package->duration_minutes }} mnt) - Rp {{ number_format($package->price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 rounded transition">
                                    ▶️ Mulai Rental
                                </button>
                            </form>

                        @else
                            {{-- RINCIAN CONSOLE AKTIF & TIMER --}}
                            <div class="text-sm space-y-2">
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
                                        data-duration="{{ $activeSession->type === 'package' && $activeSession->package ? $activeSession->package->duration_minutes : '' }}"
                                        data-extended="{{ $activeSession->extended_minutes ?? 0 }}">
                                        00:00:00
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 extra-info"></p>
                                </div>
                            </div>

                            {{-- DAFTAR ORDER FNB --}}
                            <div class="border-t pt-2">
                                <p class="text-xs font-bold text-gray-700 mb-1">Pesanan FnB:</p>
                                @if($activeSession->orders->isEmpty())
                                    <p class="text-xs text-gray-400 italic">Belum ada pesanan</p>
                                @else
                                    <ul class="text-xs space-y-1 mb-2 max-h-24 overflow-y-auto">
                                        @foreach($activeSession->orders as $order)
                                            <li class="flex justify-between border-b pb-0.5">
                                                <span>{{ $order->product->name }} (x{{$order->quantity }})</span>
                                                <span class="font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            {{-- FORM TAMBAH DURASI / PAKET (KHUSUS MODE PAKET) --}}
                            @if($activeSession->type === 'package')
                            <form action="{{ route('rental.extend', $activeSession->id) }}" method="POST" class="border-t pt-2 space-y-1">
                                @csrf
                                <label class="block text-xs font-bold text-gray-700">Perpanjang Paket:</label>
                                <div class="flex gap-1.5">
                                    <select name="package_id" class="text-xs border rounded p-1 flex-1 bg-white" required>
                                        <option value="">-- Pilih Tambah Paket --</option>
                                        @foreach($packages as $pkg)
                                            <option value="{{ $pkg->id }}">
                                                + {{ $pkg->name }} ({{ $pkg->duration_minutes }} mnt) - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-2.5 py-1 rounded font-bold transition">
                                        + Tambah
                                    </button>
                                </div>
                            </form>
                            @endif

                            {{-- FORM TAMBAH FNB --}}
                            <form action="{{ route('rental.order', $activeSession->id) }}" method="POST" class="border-t pt-2 flex gap-1.5">
                                @csrf
                                <select name="product_id" class="text-xs border rounded p-1 flex-1 bg-white" required>
                                    <option value="">-- Pilih FnB --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                            {{ $product->name }} (Stok: {{ $product->stock }}) - Rp {{ number_format($product->price, 0, ',', '.') }}
                                            {{ $product->stock <= 0 ? ' [HABIS]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantity" value="1" min="1" class="text-xs border rounded w-10 p-1 text-center" required>
                                <button type="submit" class="bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700 font-bold">
                                    +
                                </button>
                            </form>

                            {{-- FORM STOP RENTAL --}}
                            <form action="{{ route('rental.stop', $activeSession->id) }}" method="POST" class="space-y-2">
                            @csrf
                           <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Metode Pembayaran:</label>
                                <select name="payment_method" required class="w-full text-xs border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="qris">QRIS / Transfer</option>
                                </select>
                            </div>

                            <button type="submit" onclick="return confirm('Selesaikan sesi rental ini?')" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition text-sm shadow">
                                Stop & Cetak Struk
                            </button>
                        </form>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

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

    function updateTimers() {
        const timers = document.querySelectorAll('.timer-display');

        timers.forEach(timer => {
            const startTime = new Date(timer.dataset.start).getTime();
            // Ambil durasi paket dasar
            const baseDuration = timer.dataset.duration ? parseInt(timer.dataset.duration) : null;
            // Ambil durasi perpanjangan (extended_minutes)
            const extendedDuration = timer.dataset.extended ? parseInt(timer.dataset.extended) : 0;
            
            const now = new Date().getTime();

            const elapsedSeconds = Math.floor((now - startTime) / 1000);
            if (elapsedSeconds < 0) return;

            const extraInfo = timer.nextElementSibling;

            // ==========================================
            // KONDISI 1: MODE PAKET (COUNTDOWN MUNDUR)
            // ==========================================
            if (baseDuration !== null) {
                // TOTAL WAKTU PAKET = WAKTU AWAL + WAKTU PERPANJANGAN
                const totalPackageMinutes = baseDuration + extendedDuration;
                const totalPackageSeconds = totalPackageMinutes * 60;
                const remainingSeconds = totalPackageSeconds - elapsedSeconds;

                if (remainingSeconds >= 0) {
                    // Masih ada sisa waktu paket (Hitung Mundur)
                    const hours = String(Math.floor(remainingSeconds / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((remainingSeconds % 3600) / 60)).padStart(2, '0');
                    const seconds = String(remainingSeconds % 60).padStart(2, '0');

                    timer.textContent = `${hours}:${minutes}:${seconds}`;
                    timer.className = 'text-2xl font-bold tracking-wider text-green-400 timer-display';

                    if (extraInfo) {
                        extraInfo.textContent = extendedDuration > 0 ? `⏳ Sisa Waktu (+${extendedDuration} mnt)` : '⏳ Sisa Waktu Paket';
                        extraInfo.className = 'text-[10px] text-green-300 mt-1 extra-info';
                    }
                } else {
                    // Waktu Paket Habis -> Overtime
                    const overSeconds = Math.abs(remainingSeconds);
                    const hours = String(Math.floor(overSeconds / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((overSeconds % 3600) / 60)).padStart(2, '0');
                    const seconds = String(overSeconds % 60).padStart(2, '0');

                    timer.textContent = `+${hours}:${minutes}:${seconds}`;
                    timer.className = 'text-2xl font-bold tracking-wider text-red-500 animate-pulse timer-display';

                    if (extraInfo) {
                        extraInfo.textContent = '⚠️ WAKTU PAKET HABIS (OVERTIME)';
                        extraInfo.className = 'text-[10px] text-red-400 font-bold mt-1 extra-info';
                    }
                }
            } 
            // ==========================================
            // KONDISI 2: OPEN PLAY (COUNT UP MAJU)
            // ==========================================
            else {
                const hours = String(Math.floor(elapsedSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((elapsedSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(elapsedSeconds % 60).padStart(2, '0');

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
                        @elseif(($receiptSession->payment_method ?? 'cash') === 'debit')
                            Kartu Debit
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
</body>
</html>