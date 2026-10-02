@echo off
title Aplikasi Rental Billing - Full Automation
cd /d "%~dp0"

echo [1/5] Menyalakan Server PHP Laravel (Lokal)...
start /min "" php\php.exe -S 127.0.0.1:8000 -t public

:: Beri jeda 4 detik agar server lokal benar-benar siap
timeout /t 4 /nobreak >nul

echo [2/5] Membuka Aplikasi di Browser Lokal...
start http://127.0.0.1:8000

echo.
echo [3/5] Menyalakan Cloudflare Tunnel untuk Akses Publik...
echo ========================================================================
echo PENTING: JANGAN TUTUP JENDELA INI! Sistem sedang menyiapkan laporan email.
echo ========================================================================
echo.

:: Bersihkan file teks dan log lama agar selalu fresh setiap dibuka
if exist "storage\app\cloudflared_url.txt" del "storage\app\cloudflared_url.txt"
if exist "cloudflared_log.txt" del "cloudflared_log.txt"

:: Jalankan cloudflared di background dan simpan outputnya ke file log
start /b cloudflared.exe tunnel --url http://127.0.0.1:8000 > cloudflared_log.txt 2>&1

echo [4/5] Menunggu URL Cloudflare aktif tertangkap...

:: Loop pengecekan otomatis (maksimal 30 detik sampai link trycloudflare muncul)
set /a counter=0
:CheckLoop
timeout /t 3 /nobreak >nul
set /a counter+=1

:: Ambil link trycloudflare dari log dan simpan ke file teks Laravel
if exist "cloudflared_log.txt" (
    powershell -command "$content = Get-Content 'cloudflared_log.txt' -Raw; if ($content -match 'https://[a-zA-Z0-9-]+\.trycloudflare\.com') { $Matches[0] | Out-File -Encoding utf8 'storage\app\cloudflared_url.txt' }"
)

:: Jika file teks URL sudah berhasil terisi, langsung picu pengiriman email!
if exist "storage\app\cloudflared_url.txt" (
    for /f "usebackq tokens=*" %%b in ("storage\app\cloudflared_url.txt") do (
        echo.
        echo [5/5] URL Publik Didapatkan: %%b
        echo Mengirim Laporan Otomatis dan Excel ke Email...
        
        :: Bersihkan cache Laravel agar data selalu segar, lalu panggil route kirim email
        php artisan cache:clear
        php artisan config:clear
        php artisan report:send-auto
        
        echo.
        echo ========================================================
        echo [SUKSES!] Laporan dan Link Cloudflare Berhasil Terkirim!
        echo ========================================================
        goto EndScript
    )
)

:: Jika belum dapat dalam 30 detik, coba cek ulang
if %counter% LSS 10 goto CheckLoop

:EndScript
echo.
echo Sistem Rental siap digunakan. Jendela ini aman dibiarkan terbuka.
pause