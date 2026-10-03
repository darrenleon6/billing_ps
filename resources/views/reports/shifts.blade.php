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

   <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Riwayat Shift & Rekap Kas</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pantau kesesuaian uang tunai laci dengan pencatatan sistem</p>
                </div>
                <span class="text-xs font-normal text-gray-500">Total: {{ $shifts->count() }} Shift</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="p-4">Operator</th>
                            <th class="p-4">Waktu Shift</th>
                            <th class="p-4">Modal Awal</th>
                            <th class="p-4">Kas Sistem</th>
                            <th class="p-4">Fisik Laci</th>
                            <th class="p-4">Selisih</th>
                            <th class="p-4">Total QRIS</th>
                            <th class="p-4">Status</th>
                            
                            {{-- 🟢 Kolom Header Aksi Khusus Admin --}}
                            @if(auth()->check() && auth()->user()->role === 'admin')
                                <th class="p-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-4 font-bold text-gray-800">
                                    {{ $shift->user->name ?? 'Kasir' }}
                                </td>
                                <td class="p-4">
                                    <div>{{ \Carbon\Carbon::parse($shift->start_time)->format('d M Y (H:i)') }}</div>
                                    <div class="text-[10px] text-gray-400">
                                        s/d {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : 'Sekarang' }}
                                    </div>
                                </td>
                                <td class="p-4 font-semibold">
                                    Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                </td>
                                <td class="p-4 font-semibold text-gray-800">
                                    Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}
                                </td>
                                <td class="p-4 font-semibold text-blue-600">
                                    {{ $shift->actual_cash !== null ? 'Rp ' . number_format($shift->actual_cash, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-4">
                                    @if($shift->difference_cash === null)
                                        <span class="text-gray-400">-</span>
                                    @elseif($shift->difference_cash == 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">PAS</span>
                                    @elseif($shift->difference_cash > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">+Rp {{ number_format($shift->difference_cash, 0, ',', '.') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700">-Rp {{ number_format(abs($shift->difference_cash), 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="p-4 font-semibold text-indigo-600">
                                    Rp {{ number_format($shift->total_qris, 0, ',', '.') }}
                                </td>
                                <td class="p-4">
                                    @if($shift->status === 'open')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 animate-pulse">AKTIF</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">SELESAI</span>
                                    @endif
                                </td>

                                {{-- 🟢 Tombol Edit & Hapus Khusus Admin --}}
                                @if(auth()->check() && auth()->user()->role === 'admin')
                                    <td class="p-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    onclick="openShiftModal('{{ $shift->id }}', '{{ $shift->starting_cash }}', '{{ $shift->actual_cash ?? 0 }}', '{{ $shift->total_qris }}', '{{ route('shifts.update', $shift->id) }}')" 
                                                    class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm font-medium transition text-xs" 
                                                    title="Edit Shift">
                                                Edit
                                            </button>

                                            <form action="{{ route('shifts.destroy', $shift->id) }}" 
                                                method="POST" 
                                                onsubmit="return confirm('Yakin ingin menghapus riwayat shift ini?');" 
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="px-2.5 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded shadow-sm font-medium transition text-xs" 
                                                        title="Hapus Shift">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ (auth()->check() && auth()->user()->role === 'admin') ? 9 : 8 }}" class="p-6 text-center text-gray-400 italic">Belum ada data shift.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $shifts->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SHIFT --}}
    <div id="shiftEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden p-6 relative">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-bold text-gray-800">Edit Rekap Shift Kasir</h3>
                <button type="button" onclick="closeShiftModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form id="shiftEditForm" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Modal Awal (Rp)</label>
                    <input type="number" name="starting_cash" id="modalStartingCash" class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Kas Fisik Laci (Rp)</label>
                    <input type="number" name="actual_cash" id="modalActualCash" class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Total QRIS (Rp)</label>
                    <input type="number" name="total_qris" id="modalTotalQris" class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
                    <button type="button" onclick="closeShiftModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openShiftModal(id, startingCash, actualCash, totalQris, updateUrl) {
        document.getElementById('modalStartingCash').value = startingCash;
        document.getElementById('modalActualCash').value = actualCash;
        document.getElementById('modalTotalQris').value = totalQris;
        document.getElementById('shiftEditForm').action = updateUrl;
        
        document.getElementById('shiftEditModal').classList.remove('hidden');
    }

    function closeShiftModal() {
        document.getElementById('shiftEditModal').classList.add('hidden');
    }
    </script>
    </body>
</html>