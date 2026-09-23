<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing PS - Dashboard Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <div class="container mx-auto p-6">
        <h1 class="text-38px text-3xl font-bold mb-6 text-indigo-700">🎮 Dashboard Billing Rental PS</h1>

        {{-- Notifikasi Sukses / Error --}}
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- PANEL KIRI: Daftar Unit & Start Session --}}
            <div class="bg-white p-5 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">1. Mulai Rental</h2>
                <form action="{{ route('rental.start') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-medium mb-1">Pilih Unit Console</label>
                        <select name="console_id" class="w-full border rounded p-2 focus:ring focus:ring-indigo-200" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach($consoles as $console)
                                <option value="{{ $console->id }}" {{ $console->status == 'in_use' ? 'disabled' : '' }}>
                                    {{ $console->name }} (Rp {{ number_format($console->hourly_rate, 0, ',', '.') }}/jam)
                                    - {{ strtoupper($console->status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Tipe Main</label>
                        <select name="type" class="w-full border rounded p-2 focus:ring focus:ring-indigo-200" required>
                            <option value="open">Open Play (Hitung per 15 Menit)</option>
                            <option value="package">Paket</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">
                        ▶️ Mulai Sesi Rental
                    </button>
                </form>
            </div>

            {{-- PANEL TENGAH & KANAN: Sesi Aktif saat ini --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-5 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">2. Sesi Main Sedang Berjalan (Active)</h2>

                    @if($activeSessions->isEmpty())
                        <p class="text-gray-500 italic">Belum ada sesi rental yang aktif saat ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($activeSessions as $session)
                                <div class="border rounded-lg p-4 bg-gray-50 flex flex-col justify-between space-y-3">
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-bold text-lg text-indigo-800">{{ $session->console->name }}</span>
                                            <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded font-semibold">
                                                {{ strtoupper($session->type) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">Mulai: <strong>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i:s') }}</strong></p>

                                        {{-- Daftar Pesanan FnB di Sesi Ini --}}
                                        <div class="mt-3 border-t pt-2">
                                            <p class="text-xs font-semibold text-gray-700">Pesanan Makanan/Minuman:</p>
                                            @if($session->orders->isEmpty())
                                                <p class="text-xs text-gray-400 italic">Belum ada pesanan</p>
                                            @else
                                                <ul class="text-xs space-y-1 mt-1">
                                                    @foreach($session->orders as $order)
                                                        <li class="flex justify-between">
                                                            <span>• {{ $order->product->name }} (x{{ $order->quantity }})</span>
                                                            <span class="font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <p class="text-xs font-bold text-right mt-1 text-indigo-700">
                                                Total FnB: Rp {{ number_format($session->fnb_cost, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Form Tambah Order FnB --}}
                                    <form action="{{ route('rental.order', $session->id) }}" method="POST" class="border-t pt-2 flex gap-2">
                                        @csrf
                                        <select name="product_id" class="text-xs border rounded p-1 flex-1" required>
                                            <option value="">+ Tambah FnB</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }} (Rp {{ number_format($product->price, 0, ',', '.') }}) - Stok: {{ $product->stock }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="quantity" value="1" min="1" class="text-xs border rounded w-12 p-1 text-center" required>
                                        <button type="submit" class="bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700">Order</button>
                                    </form>

                                    {{-- Tombol Stop Session --}}
                                    <form action="{{ route('rental.stop', $session->id) }}" method="POST" class="pt-2">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Yakin ingin menyelesaikan sesi ini?')" class="w-full bg-red-600 text-white text-sm py-1.5 rounded font-semibold hover:bg-red-700">
                                            🛑 Stop & Hitung Tagihan
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

</body>
</html>