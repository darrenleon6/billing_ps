<?php

namespace App\Http\Controllers;

use App\Models\Console;
use Illuminate\Http\Request;

class ConsoleController extends Controller
{
    public function index()
    {
        $consoles = Console::orderBy('name', 'asc')->get();
        return view('consoles.index', compact('consoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:50',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        // Default status sesuai skema tabel kamu
        $validated['status'] = 'ready';

        Console::create($validated);

        return redirect()->back()->with('success', 'Unit konsol baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $console = Console::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:50',
            'hourly_rate' => 'required|numeric|min:0',
            'status'      => 'required|string', // Validasi string fleksibel
        ]);

        $console->update($validated);

        return redirect()->back()->with('success', 'Data konsol berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $console = Console::findOrFail($id);

        // 🟢 1. Pengecekan berdasarkan status konsol langsung
        if ($console->status === 'active') {
            return redirect()->back()->with('error', 'Gagal menghapus! Unit konsol ini sedang digunakan untuk main.');
        }

        $console->delete();

        return redirect()->back()->with('success', 'Unit konsol berhasil dihapus!');
    }
}