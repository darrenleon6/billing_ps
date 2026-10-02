<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rental Otomatis</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h3 style="color: #2c3e50; margin-bottom: 5px;">📊 LAPORAN RENTAL OTOMATIS</h3>
    
    <p style="margin: 2px 0;"><strong>Tanggal:</strong> {{ $date }}</p>
    <p style="margin: 2px 0;"><strong>Waktu:</strong> {{ $time }}</p>
    
    <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">

    <p style="margin-bottom: 5px;"><strong>=== 💰 Pendapatan Hari Ini ===</strong></p>
    <ul style="margin-top: 0; padding-left: 20px;">
        <li>Rental: Rp{{ number_format($rental, 0, ',', '.') }}</li>
        <li>FNB: Rp{{ number_format($fnb, 0, ',', '.') }}</li>
        <li><strong>TOTAL: Rp{{ number_format($grand_total, 0, ',', '.') }}</strong></li>
    </ul>

    <p style="margin-bottom: 5px;"><strong>=== 🎮 Status Unit ===</strong></p>
    <ul style="margin-top: 0; padding-left: 20px; list-style-type: none;">
        @foreach($units as $unit)
            <li style="margin-bottom: 4px;">
                🔴 <strong>{{ $unit->name }}</strong> — {{ $unit->status ?? 'Tidak Aktif' }}
            </li>
        @endforeach
    </ul>

    <!-- Tombol Akses Aplikasi via Cloudflared Otomatis -->
    <div style="margin-top: 25px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; text-align: center;">
        <h2 style="margin: 10px 0 0 0; font-size: 11px; color: #999; word-break: break-all;">Link aktif: {{ $cloudflaredUrl }}</h2>
    </div>

    <p style="margin-top: 25px; font-size: 13px; color: #7f8c8d; border-top: 1px dashed #eee; padding-top: 10px;">
        📎 <em>Lampiran: Transaksi sesi hari ini (Excel) terlampir pada email ini.</em>
    </p>
</body>
</html>