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
                
               {{-- Menu Khusus Admin Saja --}}
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('products.index') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Stok & Produk
                    </a>

                    <a href="{{ route('reports.transactions') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('reports.transactions') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Laporan Transaksi
                    </a>

                    <a href="{{ route('reports.analytics') }}" 
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors {{ request()->routeIs('reports.analytics') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        Statistik
                    </a>
                @endif

                {{-- Tombol Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium text-red-400 hover:bg-gray-800 hover:text-red-300 transition-colors">
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</nav>