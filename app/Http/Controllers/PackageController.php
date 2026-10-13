<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('items.product')->orderBy('duration_minutes', 'asc')->get();
        $products = Product::where('stock', '>', 0)->get(); // 🟢 Ambil data produk FnB

        return view('packages.index', compact('packages', 'products'));
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'name'             => 'required|string|max:255',
        'duration_minutes' => 'required|integer|min:15',
        'price'            => 'required|numeric|min:0',
        'products'         => 'nullable|array', 
        'quantities'       => 'nullable|array', 
    ]);

        DB::transaction(function () use ($request, $validated) {
        // 1. Buat paket utama (hanya simpan field yang valid untuk tabel packages)
        $package = Package::create([
            'name'             => $validated['name'],
            'duration_minutes' => $validated['duration_minutes'],
            'price'            => $validated['price'],
        ]);

        // 2. Simpan item FnB bundling jika dipilih di form
        if ($request->has('products') && is_array($request->products)) {
            foreach ($request->products as $index => $productId) {
                $qty = (int) ($request->quantities[$index] ?? 1);
                if (!empty($productId) && $qty > 0) {
                    $package->items()->create([
                        'product_id' => $productId,
                        'quantity'   => $qty,
                    ]);
                }
            }
        }
    });

    return redirect()->back()->with('success', 'Paket berhasil ditambahkan!');
}

   public function update(Request $request, $id)
{
    $package = Package::findOrFail($id);

    $validated = $request->validate([
        'name'             => 'required|string|max:255',
        'duration_minutes' => 'required|integer|min:15',
        'price'            => 'required|numeric|min:0',
        'products'         => 'nullable|array',
        'quantities'       => 'nullable|array',
    ]);

        DB::transaction(function () use ($request, $validated, $package) {
        // 1. Update data utama paket
        $package->update([
            'name'             => $validated['name'],
            'duration_minutes' => $validated['duration_minutes'],
            'price'            => $validated['price'],
        ]);

        // 2. Hapus item FnB lama, lalu sinkronkan dengan yang baru diinput
        $package->items()->delete();

        if ($request->has('products') && is_array($request->products)) {
            foreach ($request->products as $index => $productId) {
                $qty = (int) ($request->quantities[$index] ?? 1);
                if (!empty($productId) && $qty > 0) {
                    $package->items()->create([
                        'product_id' => $productId,
                        'quantity'   => $qty,
                    ]);
                }
            }
        }
    });

    return redirect()->back()->with('success', 'Data paket bundling berhasil diperbarui!');
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
