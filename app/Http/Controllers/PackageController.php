<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('duration_minutes', 'asc')->get();
        return view('packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15',
            'price'            => 'required|numeric|min:0',
        ]);

        Package::create($validated);

        return redirect()->back()->with('success', 'Paket rental baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:15',
            'price'            => 'required|numeric|min:0',
        ]);

        $package->update($validated);

        return redirect()->back()->with('success', 'Data paket berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        // Proteksi: Mencegah penghapusan jika paket sedang dipakai di sesi rental yang aktif
        if ($package->sessions()->where('status', 'active')->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Paket ini sedang digunakan dalam sesi rental yang aktif.');
        }

        $package->delete();

        return redirect()->back()->with('success', 'Paket berhasil dihapus!');
    }

}
