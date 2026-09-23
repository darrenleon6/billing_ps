<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Billing PS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

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
                                         data-duration="{{ $activeSession->type === 'package' && $activeSession->package ? $activeSession->package->duration_minutes : '' }}">
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

                            {{-- FORM TAMBAH FNB --}}
                            <form action="{{ route('rental.order', $activeSession->id) }}" method="POST" class="border-t pt-2 flex gap-1.5">
                                @csrf
                                <select name="product_id" class="text-xs border rounded p-1 flex-1" required>
                                    <option value="">+ Tambah FnB</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }} (Rp {{ number_format($product->price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantity" value="1" min="1" class="text-xs border rounded w-10 p-1 text-center" required>
                                <button type="submit" class="bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700 font-bold">
                                    +
                                </button>
                            </form>

                            {{-- TOMBOL STOP --}}
                            <form action="{{ route('rental.stop', $activeSession->id) }}" method="POST" class="pt-1">
                                @csrf
                                <button type="submit" onclick="return confirm('Selesaikan sesi {{ $console->name }}?')" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs py-2 rounded font-bold transition">
                                    🛑 Stop & Hitung Tagihan
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
            const durationMinutes = timer.dataset.duration ? parseInt(timer.dataset.duration) : null;
            const now = new Date().getTime();

            const elapsedSeconds = Math.floor((now - startTime) / 1000);
            if (elapsedSeconds < 0) return;

            const extraInfo = timer.nextElementSibling;

            // ==========================================
            // KONDISI 1: MODE PAKET (COUNTDOWN MUNDUR)
            // ==========================================
            if (durationMinutes) {
                const totalPackageSeconds = durationMinutes * 60;
                const remainingSeconds = totalPackageSeconds - elapsedSeconds;

                if (remainingSeconds >= 0) {
                    // Masih ada sisa waktu paket (Hitung Mundur)
                    const hours = String(Math.floor(remainingSeconds / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((remainingSeconds % 3600) / 60)).padStart(2, '0');
                    const seconds = String(remainingSeconds % 60).padStart(2, '0');

                    timer.textContent = `${hours}:${minutes}:${seconds}`;
                    timer.className = 'text-2xl font-bold tracking-wider text-green-400 timer-display';

                    if (extraInfo) {
                        extraInfo.textContent = '⏳ Sisa Waktu Paket';
                        extraInfo.className = 'text-[10px] text-green-300 mt-1 extra-info';
                    }
                } else {
                    // Waktu Paket Habis -> Tampilkan Overtime (Hitung Maju Kelebihan Waktu)
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

</body>
</html>