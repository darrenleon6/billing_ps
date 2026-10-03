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
                <h1 class="text-xl font-bold text-gray-800">Manajemen Promo</h1>
                <p class="text-xs text-gray-500">Kelola daftar promo diskon nominal, persen, dan bonus waktu sewa PS.</p>
            </div>
            <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition shadow-md flex items-center gap-2">
                    ➕ <span>Tambah Promo Baru</span>
                </button>
        </div>

            {{-- Alert Success / Error --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- TABEL DAFTAR PROMO --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="p-3">Kode & Nama Promo</th>
                                    <th class="p-3">Tipe & Benefit</th>
                                    <th class="p-3">Syarat & Ketentuan</th>
                                    <th class="p-3">Masa Berlaku</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($promotions as $promo)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-3">
                                            <span class="inline-block bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded text-[11px] border border-indigo-200 mb-1">
                                                {{ $promo->code }}
                                            </span>
                                            <div class="font-bold text-gray-800 text-sm">{{ $promo->name }}</div>
                                        </td>
                                        <td class="p-3 font-semibold text-gray-700">
                                            @if($promo->type === 'bonus_time')
                                                <span class="text-emerald-600 font-bold">⏱️ +{{ $promo->bonus_minutes }} Menit Gratis</span>
                                            @elseif($promo->type === 'discount_nominal')
                                                <span class="text-amber-600 font-bold">💵 Potongan Rp {{ number_format($promo->discount_value, 0, ',', '.') }}</span>
                                            @elseif($promo->type === 'discount_percent')
                                                <span class="text-blue-600 font-bold">🏷️ Diskon {{ $promo->discount_value }}%</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-gray-600">
                                            <ul class="list-disc list-inside space-y-0.5">
                                                @if($promo->min_duration_minutes > 0)
                                                    <li>Min. Sewa: <b>{{ $promo->min_duration_minutes }} Mnt</b></li>
                                                @endif
                                                @if($promo->min_transaction_amount > 0)
                                                    <li>Min. Tagihan: <b>Rp {{ number_format($promo->min_transaction_amount, 0, ',', '.') }}</b></li>
                                                @endif
                                                @if($promo->min_duration_minutes == 0 && $promo->min_transaction_amount == 0)
                                                    <span class="text-gray-400">Tanpa Syarat</span>
                                                @endif
                                            </ul>
                                        </td>
                                        <td class="p-3 text-gray-600">
                                            @if($promo->start_date || $promo->end_date)
                                                {{ $promo->start_date ? date('d/m/Y', strtotime($promo->start_date)) : 'Awal' }}
                                                s/d
                                                {{ $promo->end_date ? date('d/m/Y', strtotime($promo->end_date)) : 'Selamanya' }}
                                            @else
                                                <span class="text-gray-400">Selamanya</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            @if($promo->is_active)
                                                <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Aktif</span>
                                            @else
                                                <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-[10px] font-bold">Non-Aktif</span>
                                            @endif
                                        </td>

                                        <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                {{-- Tombol Edit --}}
                                                <button type="button" 
                                                        onclick='openEditModal(@json($promo))' 
                                                        class="text-amber-500 hover:text-amber-700 font-bold transition">
                                                    Edit
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('promotions.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
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
                                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada promo yang dibuat. Klik tombol di atas untuk menambah promo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL TAMBAH PROMO --}}
    <div id="createPromoModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-gray-800 mb-4">Tambah Promo Baru</h3>

            <form action="{{ route('promotions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kode Promo</label>
                        <input type="text" name="code" placeholder="Misal: BONUS1JAM" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 uppercase focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Promo</label>
                        <input type="text" name="name" placeholder="Misal: Main 2 Jam Gratis 1 Jam" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Promo</label>
                    <select name="type" id="promoTypeSelect" onchange="togglePromoFields()" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                        <option value="bonus_time">⏱️ Bonus Tambah Waktu (Menit Gratis)</option>
                        <option value="discount_nominal">💵 Potongan Harga Nominal (Rp)</option>
                        <option value="discount_percent">🏷️ Diskon Persentase (%)</option>
                    </select>
                </div>

                <div id="bonusTimeContainer">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Menit Gratis</label>
                    <input type="number" name="bonus_minutes" placeholder="60 (artinya gratis 1 jam)" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div id="discountValueContainer" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 mb-1" id="discountValueLabel">Nilai Potongan (Rp)</label>
                    <input type="number" name="discount_value" placeholder="10000" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <hr class="my-2 border-gray-100">
                <p class="text-xs font-bold text-indigo-600">Syarat & Ketentuan Promo (Opsional):</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Min. Durasi Sewa (Menit)</label>
                        <input type="number" name="min_duration_minutes" placeholder="120 (min 2 jam)" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Min. Total Tagihan (Rp)</label>
                        <input type="number" name="min_transaction_amount" placeholder="30000" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Berlaku Mulai</label>
                        <input type="date" name="start_date" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Berlaku Sampai</label>
                        <input type="date" name="end_date" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Simpan Promo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT PROMO --}}
    <div id="editPromoModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-gray-800 mb-4">Edit Promo</h3>

            <form id="editPromoForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kode Promo</label>
                        <input type="text" id="editCode" name="code" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 uppercase focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Promo</label>
                        <input type="text" id="editName" name="name" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Promo</label>
                        <select name="type" id="editPromoTypeSelect" onchange="toggleEditPromoFields()" required class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="bonus_time">⏱️ Bonus Tambah Waktu (Menit Gratis)</option>
                            <option value="discount_nominal">💵 Potongan Harga Nominal (Rp)</option>
                            <option value="discount_percent">🏷️ Diskon Persentase (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status Promo</label>
                        <select name="is_active" id="editIsActive" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div id="editBonusTimeContainer">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Menit Gratis</label>
                    <input type="number" id="editBonusMinutes" name="bonus_minutes" placeholder="60" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div id="editDiscountValueContainer" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 mb-1" id="editDiscountValueLabel">Nilai Potongan</label>
                    <input type="number" id="editDiscountValue" name="discount_value" placeholder="10000" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                </div>

                <hr class="my-2 border-gray-100">
                <p class="text-xs font-bold text-indigo-600">Syarat & Ketentuan Promo (Opsional):</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Min. Durasi Sewa (Menit)</label>
                        <input type="number" id="editMinDuration" name="min_duration_minutes" placeholder="120" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Min. Total Tagihan (Rp)</label>
                        <input type="number" id="editMinTransaction" name="min_transaction_amount" placeholder="30000" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Berlaku Mulai</label>
                        <input type="date" id="editStartDate" name="start_date" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Berlaku Sampai</label>
                        <input type="date" id="editEndDate" name="end_date" class="w-full text-xs border border-gray-300 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500">
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
            document.getElementById('createPromoModal').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('createPromoModal').classList.add('hidden');
        }
        function togglePromoFields() {
            const type = document.getElementById('promoTypeSelect').value;
            const bonusContainer = document.getElementById('bonusTimeContainer');
            const discountContainer = document.getElementById('discountValueContainer');
            const discountLabel = document.getElementById('discountValueLabel');

            if (type === 'bonus_time') {
                bonusContainer.classList.remove('hidden');
                discountContainer.classList.add('hidden');
            } else {
                bonusContainer.classList.add('hidden');
                discountContainer.classList.remove('hidden');
                discountLabel.innerText = type === 'discount_nominal' ? 'Nilai Potongan (Rp)' : 'Persentase Diskon (%)';
            }
        }

        function openEditModal(promo) {
            const form = document.getElementById('editPromoForm');
            form.action = `/promotions/${promo.id}`;

            document.getElementById('editCode').value = promo.code;
            document.getElementById('editName').value = promo.name;
            document.getElementById('editPromoTypeSelect').value = promo.type;
            document.getElementById('editIsActive').value = promo.is_active ? "1" : "0";
            document.getElementById('editBonusMinutes').value = promo.bonus_minutes ?? 0;
            document.getElementById('editDiscountValue').value = promo.discount_value ?? 0;
            document.getElementById('editMinDuration').value = promo.min_duration_minutes ?? 0;
            document.getElementById('editMinTransaction').value = promo.min_transaction_amount ?? 0;
            document.getElementById('editStartDate').value = promo.start_date ?? '';
            document.getElementById('editEndDate').value = promo.end_date ?? '';

            toggleEditPromoFields();
            document.getElementById('editPromoModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editPromoModal').classList.add('hidden');
        }

        function toggleEditPromoFields() {
            const type = document.getElementById('editPromoTypeSelect').value;
            const bonusContainer = document.getElementById('editBonusTimeContainer');
            const discountContainer = document.getElementById('editDiscountValueContainer');
            const discountLabel = document.getElementById('editDiscountValueLabel');

            if (type === 'bonus_time') {
                bonusContainer.classList.remove('hidden');
                discountContainer.classList.add('hidden');
            } else {
                bonusContainer.classList.add('hidden');
                discountContainer.classList.remove('hidden');
                discountLabel.innerText = type === 'discount_nominal' ? 'Nilai Potongan (Rp)' : 'Persentase Diskon (%)';
            }
        }
    </script>
    </body>
</html>