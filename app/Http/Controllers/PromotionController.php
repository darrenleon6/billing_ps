<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // Tampilkan daftar semua promo
    public function index()
    {
        $promotions = Promotion::latest()->get();
        return view('promotions.index', compact('promotions'));
    }

    // Simpan promo baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:promotions,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:discount_nominal,discount_percent,bonus_time',
            'discount_value' => 'nullable|numeric|min:0',
            'bonus_minutes' => 'nullable|integer|min:0',
            'min_duration_minutes' => 'nullable|integer|min:0',
            'min_transaction_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Set nilai default 0 jika input dikirim kosong (null)
        $validated['discount_value'] = $request->input('discount_value', 0) ?? 0;
        $validated['bonus_minutes'] = $request->input('bonus_minutes', 0) ?? 0;
        $validated['min_duration_minutes'] = $request->input('min_duration_minutes', 0) ?? 0;
        $validated['min_transaction_amount'] = $request->input('min_transaction_amount', 0) ?? 0;

        Promotion::create($validated);

        return redirect()->back()->with('success', 'Promo berhasil ditambahkan!');
    }

    // Update promo
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:promotions,code,' . $id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:discount_nominal,discount_percent,bonus_time',
            'discount_value' => 'nullable|numeric|min:0',
            'bonus_minutes' => 'nullable|integer|min:0',
            'min_duration_minutes' => 'nullable|integer|min:0',
            'min_transaction_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $validated['discount_value'] = $request->input('discount_value', 0) ?? 0;
        $validated['bonus_minutes'] = $request->input('bonus_minutes', 0) ?? 0;
        $validated['min_duration_minutes'] = $request->input('min_duration_minutes', 0) ?? 0;
        $validated['min_transaction_amount'] = $request->input('min_transaction_amount', 0) ?? 0;

        $promotion->update($validated);

        return redirect()->back()->with('success', 'Promo berhasil diperbarui!');
    }

    // Hapus promo
    public function destroy($id)
    {
        Promotion::destroy($id);
        return redirect()->back()->with('success', 'Promo berhasil dihapus!');
    }
}
