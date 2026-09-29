<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Shift Operator') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Riwayat Shift & Rekap Kas</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pantau kesesuaian uang tunai laci dengan pencatatan sistem</p>
                </div>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-gray-400 italic">Belum ada data shift.</td>
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
</x-app-layout>