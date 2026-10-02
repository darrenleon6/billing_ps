<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ReportController;

class SendReportCommand extends Command
{
    // Ini adalah nama perintah yang akan dipanggil di file .bat
    protected $signature = 'report:send-auto';

    // Deskripsi singkat tentang perintah ini
    protected $description = 'Kirim laporan rental otomatis dan link Cloudflare via API';

    public function handle()
    {
        $this->info('Memproses pengiriman laporan otomatis...');

        try {
            // Panggil Controller tempat fungsi laporanmu berada
            $controller = new ReportController();
            
            // Panggil fungsi kirim laporan yang sudah kamu buat sebelumnya
            $controller->sendAutoDailyReport();
            
            $this->info('SUKSES: Laporan otomatis berhasil dikirim!');
        } catch (\Exception $e) {
            $this->error('GAGAL: ' . $e->getMessage());
        }
    }
}
