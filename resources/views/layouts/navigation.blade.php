<nav class="bg-gray-900 border-b border-gray-800 text-white shadow-lg sticky top-0 z-40 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            {{-- Brand / Logo --}}
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 text-white font-black px-3 py-1.5 rounded-lg tracking-wider text-base shadow">
                    PS
                </div>
                <span class="font-bold text-lg tracking-wide text-white">RENTAL PS KITA</span>
            </div>

            {{-- Nav Links --}}
            <div class="flex items-center space-x-2 sm:space-x-4">
                {{-- Link Dashboard --}}
                <a href="{{ route('dashboard') }}" 
                   class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    Dashboard Rental
                </a>

                <!-- Link Laporan Shift Operator -->
                <a href="{{ route('reports.shifts') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('reports.shifts') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    Laporan Shift
                </a>

                <a href="{{ route('reports.transactions') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('reports.transactions') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Laporan Transaksi
                </a>
                
               {{-- Menu Khusus Admin Saja --}}
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('products.index') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Stok & Produk
                    </a>


                    <a href="{{ route('reports.analytics') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('reports.analytics') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Statistik
                    </a>
                @endif

                {{-- Tombol Logout dengan Proteksi Shift --}}
                @php
                    $openShift = \App\Models\Shift::where('user_id', auth()->id())
                        ->where('status', 'open')
                        ->exists();
                @endphp

                @if($openShift)
                    {{-- Jika shift masih buka, tampilkan tombol terproteksi --}}
                    <button type="button" 
                            onclick="alert('Gagal Logout! Kamu wajib menutup (Stop) Shift saat ini terlebih dahulu sebelum keluar dari sistem.')" 
                            class="text-xs bg-gray-200 text-gray-500 font-semibold px-3 py-1.5 rounded-lg cursor-not-allowed">
                        🔒 Logout
                    </button>
                @else
                    {{-- Jika shift sudah ditutup / tidak ada shift aktif, tombol logout berfungsi normal --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg transition shadow-xs">
                            Logout
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</nav>