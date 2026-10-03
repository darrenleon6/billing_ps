    {{-- TOMBOL HAMBURGER MELAYANG (Tampil di Pojok Kiri Atas) --}}
    <button type="button"
            onclick="toggleSidebar()"
            class="fixed top-4 left-4 z-40 bg-slate-900/90 backdrop-blur-md p-2.5 rounded-2xl shadow-xl hover:bg-slate-800 transition-all duration-200 cursor-pointer flex items-center justify-center border border-slate-700/50 hover:scale-105 active:scale-95"  style="color: #3790f4;">
        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- OVERLAY LATAR BELAKANG GELAP SAAT SIDEBAR DIBUKA (Opsional tapi keren) --}}
    <div id="sidebar-overlay" 
        onclick="toggleSidebar()" 
        class="fixed inset-0 bg-slate-950/50 backdrop-blur-xs z-40 transition-opacity duration-300 opacity-0 pointer-events-none">
    </div>

    {{-- SIDEBAR UTAMA (Model Slide-Over Modern) --}}
    <aside id="sidebar-menu"
        style="transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
        class="w-72 bg-gradient-to-b from-[#0f172a] to-[#1e1b4b] text-white flex flex-col h-screen fixed top-0 left-0 border-r border-slate-800/80 z-50 shadow-2xl">
        
        {{-- LOGO BRANDING & TOMBOL CLOSE X --}}
        <div class="p-5 border-b border-slate-800/80 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                {{-- 🟢 GAMBAR LOGO GUARD PLAYSTATION --}}
                <img src="{{ asset('images/logo-guard.png') }}" alt="Guard PlayStation Logo" class="h-14 w-auto object-contain rounded-xl">
                {{-- 🟢 TEKS BRANDING NAMA RENTAL --}}
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm tracking-wider" style="color: #3790f4;">GUARD</span>
                    <span class="text-[9px] tracking-widest text-white-400 uppercase font-bold">PLAYSTATION</span>
                </div>
            </div>

            {{-- Tombol X Close --}}
            <button type="button" 
                    onclick="toggleSidebar()"
                    class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    {{-- MENU-MENU SIDEBAR (Desain Pill Active yang Cantik) --}}
    <div class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
        <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">
            Menu Utama
        </p>

        <a href="{{ route('dashboard') }}" 
           class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
            <span class="text-base">🖥️</span>
            <span>Dashboard Rental</span>
        </a>

        <a href="{{ route('reports.shifts') }}" 
           class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('reports.shifts') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
            <span class="text-base">📋</span>
            <span>Laporan Shift</span>
        </a>

        <a href="{{ route('reports.transactions') }}" 
           class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('reports.transactions') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
            <span class="text-base">🧾</span>
            <span>Laporan Transaksi</span>
        </a>

        @if(auth()->user()->isAdmin())
            <div class="pt-6 pb-2">
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">
                    Kelola Admin
                </p>
            </div>

            <a href="{{ route('products.index') }}" 
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="text-base">🍿</span>
                <span>Stok & Produk</span>
            </a>

            <a href="{{ route('reports.analytics') }}" 
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('reports.analytics') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="text-base">📊</span>
                <span>Statistik Analytics</span>
            </a>

            <a href="{{ route('promotions.index') }}" 
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('promotions.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="text-base">🏷️</span>
                <span>Promo & Diskon</span>
            </a>

            <a href="{{ route('consoles.index') }}" 
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('consoles.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="text-base">🎮</span>
                <span>Kelola Konsol</span>
            </a>

            <a href="{{ route('packages.index') }}" 
               class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-bold transition-all duration-200 {{ request()->routeIs('packages.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 translate-x-1' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="text-base">📦</span>
                <span>Paket Rental</span>
            </a>
        @endif
    </div>

    {{-- TOMBOL LOGOUT --}}
    <div class="p-4 border-t border-slate-800/80 shrink-0 bg-slate-950/30">
        @php
            $openShift = \App\Models\Shift::where('user_id', auth()->id())
                ->where('status', 'open')
                ->exists();
        @endphp

        @if($openShift)
            <button type="button" 
                    onclick="alert('Gagal Logout! Kamu wajib menutup (Stop) Shift saat ini terlebih dahulu sebelum keluar dari sistem.')" 
                    class="w-full flex items-center justify-center gap-2 text-xs bg-slate-800/60 text-slate-400 font-bold px-4 py-3 rounded-2xl cursor-not-allowed border border-slate-700/50">
                🔒 <span>Logout (Shift Aktif)</span>
            </button>
        @else
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-xs bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white font-bold px-4 py-3 rounded-2xl transition duration-200 border border-red-500/20 shadow-lg shadow-red-500/10">
                    🚪 <span>Logout</span>
                </button>
            </form>
        @endif
    </div>
</aside>

{{-- SCRIPT INTERAKTIF HALUS (TOGGLE SIDEBAR) --}}
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebar.style.transform === 'translateX(0px)') {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
        } else {
            sidebar.style.transform = 'translateX(0px)';
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
        }
    }
</script>