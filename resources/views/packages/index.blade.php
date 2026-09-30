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
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-600">Kelola daftar paket rental, durasi main, dan harga paket tetap.</p>
                <button type="button" onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition shadow-md flex items-center gap-2">
                    ➕ <span>Tambah Paket Baru</span>
                </button>
            </div>

            {{-- TABEL DATA PAKET --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="p-3">Nama Paket</th>
                                    <th class="p-3">Durasi</th>
                                    <th class="p-3">Harga Paket</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($packages as $package)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-3 font-bold text-gray-800 text-sm">
                                            📦 {{ $package->name }}
                                        </td>
                                        <td class="p-3 font-semibold text-gray-600">
                                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-[11px] border border-indigo-100 font-bold">
                                                ⏱️ {{ $package->duration_minutes }} Menit ({{ round($package->duration_minutes / 60, 1) }} Jam)
                                            </span>
                                        </td>
                                        <td class="p-3 font-bold text-green-600 text-sm">
                                            Rp {{ number_format($package->price, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <button type="button" 
                                                        onclick='openEditModal(@json($package))' 
                                                        class="text-amber-500 hover:text-amber-700 font-bold transition">
                                                    Edit
                                                </button>

                                                <form action="{{ route('packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-gray-400">Belum ada paket rental. Klik tombol di atas untuk menambahkan paket pertama.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL TAMBAH PAKET --}}
    <div id="createPackageModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
            <h3 class="text-base font-bold text-gray-800 mb-4">Tambah Paket Rental Baru</h3>
            
            <form action="{{ route('packages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Paket</label>
                    <input type="text" name="name" placeholder="Misal: Paket Hemat 2 Jam" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Durasi (Menit)</label>
                        <input type="number" name="duration_minutes" placeholder="120" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Harga Paket (Rp)</label>
                        <input type="number" name="price" placeholder="18000" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Simpan Paket
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT PAKET --}}
    <div id="editPackageModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
            <h3 class="text-base font-bold text-gray-800 mb-4">Edit Paket Rental</h3>
            
            <form id="editPackageForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Paket</label>
                    <input type="text" id="editName" name="name" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Durasi (Menit)</label>
                        <input type="number" id="editDurationMinutes" name="duration_minutes" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Harga Paket (Rp)</label>
                        <input type="number" id="editPrice" name="price" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
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
            document.getElementById('createPackageModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createPackageModal').classList.add('hidden');
        }

        function openEditModal(packageData) {
            document.getElementById('editPackageForm').action = `/packages/${packageData.id}`;
            document.getElementById('editName').value = packageData.name;
            document.getElementById('editDurationMinutes').value = packageData.duration_minutes;
            document.getElementById('editPrice').value = packageData.price;
            document.getElementById('editPackageModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editPackageModal').classList.add('hidden');
        }
    </script>
    </body>
</html>