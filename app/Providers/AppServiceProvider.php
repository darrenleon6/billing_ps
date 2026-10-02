<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Tambahkan dua baris ini di atas
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void // Tambahkan Request $request di sini
    {
        // --- MULAI DITAMBAHKAN DI SINI ---
        
        // Deteksi jika aplikasi berjalan di lingkungan 'local' (di komputer kita)
        // dan jika ada header 'x-forwarded-proto' yang biasa dikirim Cloudflare
        if ($this->app->environment('local') && $request->server('HTTP_X_FORWARDED_PROTO')) {
            // Paksa URL menggunakan skema HTTPS
            URL::forceScheme('https');
        }
        
        // --- SELESAI DITAMBAHKAN DI SINI ---
    }
}