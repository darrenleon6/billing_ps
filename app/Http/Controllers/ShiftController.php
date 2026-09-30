<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\RentalSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    // Buka Shift / Clock-In
   public function start(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ]);

        // 1. Cek apakah user punya shift yang masih open
        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();
        if ($activeShift) {
            return back()->with('error', 'Kamu masih memiliki shift yang belum ditutup!');
        }

        // 2. Buat shift baru untuk kasir yang bertugas
        $newShift = Shift::create([
            'user_id'       => Auth::id(),
            'start_time'     => Carbon::now(),
            'starting_cash' => $request->starting_cash,
            'status'        => 'open',
        ]);

        // 3. 🟢 OPSI 1: Pindahkan semua sesi rental yang MASIH AKTIF ke Shift Baru
        RentalSession::where('status', 'active')
            ->update([
                'shift_id' => $newShift->id
            ]);

        return back()->with('success', 'Shift berhasil dibuka. Sesi rental aktif telah dialihkan ke shift kamu!');
    }
    // Tutup Shift / Clock-Out
    public function stop(Request $request, Shift $shift)
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // 1. Ambil semua sesi rental yang SELESAI pada shift ini
        $sessions = RentalSession::where('shift_id', $shift->id)
            ->where('status', 'completed')
            ->get();

        // 2. Cukup jumlahkan pecahan nominal cash & QRIS langsung dari kolomnya
        // (Sudah otomatis mencakup Rental + FnB + Split Payment)
        $totalCashRevenue = $sessions->sum('cash_amount');
        $totalQrisRevenue = $sessions->sum('qris_amount');

        // 3. Kalkulasi Kas Sistem (Modal Awal + Total Pemasukan Cash)
        $expectedCash = $shift->starting_cash + $totalCashRevenue;
        $actualCash = (float) $request->actual_cash;
        $difference = $actualCash - $expectedCash;

        // 4. Simpan Hasil Rekap Shift
        $shift->update([
            'end_time'        => \Carbon\Carbon::now(),
            'expected_cash'   => $expectedCash,
            'actual_cash'     => $actualCash,
            'difference_cash' => $difference,
            'total_qris'      => $totalQrisRevenue,
            'status'          => 'closed',
            'notes'           => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Shift berhasil ditutup!');
    }

    // Tampilkan Riwayat Shift
    public function index()
    {  
        $user = auth()->user();

        $shifts = Shift::with('user')
            // Jika bukan admin (operator), hanya tampilkan shift miliknya sendiri
            ->when($user->role !== 'admin', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest('start_time')
            ->paginate(10);

        return view('reports.shifts', compact('shifts'));
    }
    
}