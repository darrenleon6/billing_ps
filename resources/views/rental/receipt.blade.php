{{-- SECTION RINCIAN BIAYA RENTAL DI STRUK --}}
<div class="border-b border-dashed pb-2 mb-2 text-xs">
    <p class="font-bold mb-1">Rincian Sewa:</p>
    
    @if($sessionReceipt->type === 'package' && $sessionReceipt->package)
        {{-- Paket Awal --}}
        <div class="flex justify-between">
            <span>{{ $sessionReceipt->package->name }} ({{ $sessionReceipt->package->duration_minutes }} mnt)</span>
            <span>Rp {{ number_format($sessionReceipt->package->price, 0, ',', '.') }}</span>
        </div>

        {{-- Paket Perpanjangan (Jika Ada) --}}
        @if($sessionReceipt->extended_minutes > 0)
            <div class="flex justify-between text-amber-700 font-medium">
                <span>+ Perpanjang: {{ $sessionReceipt->extended_package_name ?? 'Tambahan Waktu' }} ({{ $sessionReceipt->extended_minutes }} mnt)</span>
                <span>Rp {{ number_format($sessionReceipt->extended_cost, 0, ',', '.') }}</span>
            </div>
        @endif
    @else
        {{-- Open Play --}}
        <div class="flex justify-between">
            <span>Open Play ({{ $sessionReceipt->total_minutes }} mnt)</span>
            <span>Rp {{ number_format($sessionReceipt->rental_cost, 0, ',', '.') }}</span>
        </div>
    @endif
</div>