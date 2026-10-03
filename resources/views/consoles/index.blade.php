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

     <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Manajemen Konsol</h1>
                <p class="text-xs text-gray-500">Kelola daftar unit PS, jenis konsol, dan tarif sewa per jam.</p>
            </div>
            <button type="button" onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition shadow-md flex items-center gap-2">
                    ➕ <span>Tambah Konsol Baru</span>
            </button>
        </div>

            {{-- Alert Notifikasi --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative text-xs font-semibold">
                    {{ session('error') }}
                </div>
            @endif

          

            {{-- TABEL DATA KONSOL --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="p-3">Nama Unit</th>
                                    <th class="p-3">Tipe/Jenis</th>
                                    <th class="p-3">Tarif / Jam</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($consoles as $console)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-3 font-bold text-gray-800 text-sm">
                                            🎮 {{ $console->name }}
                                        </td>
                                        <td class="p-3 font-semibold text-gray-600">
                                            <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md text-[11px] border border-gray-200 font-bold">
                                                {{ strtoupper($console->type) }}
                                            </span>
                                        </td>
                                        <td class="p-3 font-bold text-indigo-600">
                                            Rp {{ number_format($console->hourly_rate, 0, ',', '.') }} / jam
                                        </td>
                                        <td class="p-3 text-center">
                                            @if($console->status === 'ready')
                                                <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Ready / Kosong</span>
                                            @elseif($console->status === 'active')
                                                <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Sedang Main</span>
                                            @else
                                                <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Maintenance</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                {{-- Tombol Edit --}}
                                                <button type="button" 
                                                        onclick='openEditModal(@json($console))' 
                                                        class="text-amber-500 hover:text-amber-700 font-bold transition">
                                                    Edit
                                                </button>

                                                {{-- Tombol Hapus (Disable jika status 'active') --}}
                                                @if($console->status === 'active')
                                                    <span class="text-gray-300 font-bold cursor-not-allowed" title="Konsol sedang aktif digunakan, tidak dapat dihapus">
                                                        Hapus
                                                    </span>
                                                @else
                                                    <form action="{{ route('consoles.destroy', $console->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus unit konsol ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-gray-400">Belum ada unit konsol. Klik tombol di atas untuk menambahkan unit pertama.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL TAMBAH KONSOL --}}
    <div id="createConsoleModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
            <h3 class="text-base font-bold text-gray-800 mb-4">Tambah Unit Konsol Baru</h3>
            
            <form action="{{ route('consoles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="name" placeholder="Misal: PS5 - Unit 01" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tipe / Jenis</label>
                        <select name="type" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="PS3">PS3</option>
                            <option value="PS4">PS4</option>
                            <option value="PS5">PS5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tarif Per Jam (Rp)</label>
                        <input type="number" name="hourly_rate" placeholder="10000" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Simpan Unit
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT KONSOL --}}
    <div id="editConsoleModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
            <h3 class="text-base font-bold text-gray-800 mb-4">Edit Unit Konsol</h3>
            
            <form id="editConsoleForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" id="editName" name="name" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tipe / Jenis</label>
                        <select id="editType" name="type" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="PS3">PS3</option>
                            <option value="PS4">PS4</option>
                            <option value="PS5">PS5</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tarif Per Jam (Rp)</label>
                        <input type="number" id="editHourlyRate" name="hourly_rate" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Unit</label>
                    <select id="editStatus" name="status" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                        <option value="ready">Ready (Tersedia)</option>
                        <option value="active">Sedang Main (Active)</option>
                        <option value="maintenance">Maintenance / Rusak</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createConsoleModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createConsoleModal').classList.add('hidden');
        }

        function openEditModal(console) {
            document.getElementById('editConsoleForm').action = `/consoles/${console.id}`;
            document.getElementById('editName').value = console.name;
            document.getElementById('editType').value = console.type;
            document.getElementById('editHourlyRate').value = console.hourly_rate;
            document.getElementById('editStatus').value = console.status;
            document.getElementById('editConsoleModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editConsoleModal').classList.add('hidden');
        }
    </script>
    </body>
</html>