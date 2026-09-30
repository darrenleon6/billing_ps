<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Shift;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // 1. Cek apakah user yang login masih memiliki shift dengan status 'open'
        $hasActiveShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        // 2. Jika masih ada shift aktif, cegah logout dan kirim pesan peringatan
        if ($hasActiveShift) {
            return redirect()->back()->with('error', 'Gagal Logout! Kamu wajib menutup (Stop) Shift terlebih dahulu sebelum keluar dari sistem.');
        }

        // 3. Proses logout standar Laravel jika shift sudah ditutup
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
