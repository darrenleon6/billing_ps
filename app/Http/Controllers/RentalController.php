<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Console;
use App\Models\Product;
use App\Models\Package;
use App\Models\RentalSession;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Shift; // 🟢 Jangan lupa import model Shift di bagian atas controller
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Promotion; // 👈 Pastikan import Model Promotion ditaruh di paling atas file

class RentalController extends Controller
{

    public function index()
    {
        // 1. Ambil semua console beserta sesi aktif
        $consoles = Console::with(['sessions' => function ($query) {
            $query->where('status', 'active')->with(['orders.product', 'package']);
        }])->orderBy('name', 'asc')->get();

        // 2. Ambil produk FnB & Paket
        $products = Product::orderBy('name', 'asc')->get();
        $packages = Package::all();

        // 🟢 3. Ambil daftar konsol yang kosong (available) untuk Modal Pindah Konsol
        $availableConsoles = Console::whereDoesntHave('sessions', function ($query) {
            $query->where('status', 'active');
        })->orderBy('name', 'asc')->get();

        // 🟢 4. Ambil daftar promo yang sedang AKTIF untuk dipilihi operator
        $promotions = Promotion::where('is_active', true)->orderBy('name', 'asc')->get();

        // 5. Cek apakah ada request cetak/tampil struk
        $receiptSession = null;
        if (session('show_receipt_id')) {
            $receiptSession = RentalSession::with(['console', 'package', 'orders.product', 'promotion'])
                ->find(session('show_receipt_id'));
        }

        // 🟢 6. Masukkan 'promotions' ke dalam compact()
        return view('dashboard', compact(
            'consoles', 
            'products', 
            'packages', 
            'receiptSession', 
            'availableConsoles', 
            'promotions'
        ));
    }

    public function startSession(Request $request)
    {
        // 🟢 1. Cek apakah ada shift yang sedang AKTIF untuk user/kasir yang login
        $activeShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->back()->with('error', 'Gagal memulai rental! Kamu harus Buka Shift terlebih dahulu.');
        }

        // 🟢 2. Validasi Input (termasuk promotion_id)
        $request->validate([
            'console_id'   => 'required|exists:consoles,id',
            'type'         => 'required|in:open,package',
            'package_id'   => 'nullable|required_if:type,package|exists:packages,id',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        // Pastikan tidak ada sesi aktif di console yang sama
        $activeSession = RentalSession::where('console_id', $request->console_id)
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return redirect()->back()->with('error', 'Console ini sedang aktif digunakan!');
        }

        // 🟢 3. Logika Pengecekan & Pengaplikasian Promo Bonus Waktu
        $bonusMinutes = 0;
        $appliedPromoId = null;

        if ($request->filled('promotion_id')) {
            $promo = \App\Models\Promotion::find($request->promotion_id);

            if ($promo && $promo->is_active && $promo->type === 'bonus_time') {
                // Hitung durasi dasar (jika memilih paket)
                $baseDuration = 0;
                if ($request->type === 'package' && $request->filled('package_id')) {
                    $package = \App\Models\Package::find($request->package_id);
                    $baseDuration = $package ? $package->duration_minutes : 0;
                }

                // Validasi Syarat Minimal Durasi Sewa Promo
                if ($promo->min_duration_minutes > 0 && $baseDuration < $promo->min_duration_minutes) {
                    return redirect()->back()->with('error', "Gagal menerapkan promo! Promo '{$promo->name}' membutuhkan minimal pilihan paket {$promo->min_duration_minutes} menit.");
                }

                // Cek tanggal berlaku promo
                $today = now()->toDateString();
                if (($promo->start_date && $today < $promo->start_date) || ($promo->end_date && $today > $promo->end_date)) {
                    return redirect()->back()->with('error', 'Gagal menerapkan promo! Masa berlaku promo ini telah berakhir.');
                }

                $bonusMinutes = $promo->bonus_minutes;
                $appliedPromoId = $promo->id;
            }
        }
        $console = Console::findOrFail($request->console_id);
        $console->update(['status' => 'active']);
        // 🟢 4. Simpan Sesi Rental Baru
        RentalSession::create([
            'console_id'       => $request->console_id,
            'user_id'          => Auth::id(),        // Catat kasir yang melayani
            'shift_id'         => $activeShift->id,  // Kaitkan ke Shift Aktif
            'package_id'       => $request->type === 'package' ? $request->package_id : null,
            'promotion_id'     => $appliedPromoId,   // 👈 Simpan ID promo bonus waktu jika ada
            'extended_minutes' => $bonusMinutes,     // 👈 Masukkan bonus menit langsung ke extended_minutes agar timer otomatis bertambah
            'type'             => $request->type,
            'start_time'       => Carbon::now(),
            'status'           => 'active',
            'notes'            => $request->input('notes'),
        ]);

        $successMessage = 'Sesi rental berhasil dimulai!';
        if ($bonusMinutes > 0) {
            $successMessage .= " (Bonus promo +{$bonusMinutes} menit berhasil diterapkan)";
        }

        return redirect()->back()->with('success', $successMessage);
    }
    public function stopSession(Request $request, $id)
    {
        // 0. VALIDASI SHIFT: Pastikan kasir memiliki Shift Aktif yang sedang terbuka
        $activeShift = \App\Models\Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->back()->with('error', 'Transaksi gagal! Kamu harus membuka Shift terlebih dahulu sebelum menghentikan sesi.');
        }

        // 1. Validasi input metode pembayaran & promo
        $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,qris,split'],
            'cash_amount'    => ['required_if:payment_method,split', 'nullable', 'numeric', 'min:0'],
            'qris_amount'    => ['required_if:payment_method,split', 'nullable', 'numeric', 'min:0'],
            'promotion_id'   => ['nullable', 'exists:promotions,id'], // 👈 Validasi promo
        ]);

        $session = RentalSession::with(['console', 'package', 'orders'])->findOrFail($id);

        $endTime = Carbon::now();
        $startTime = Carbon::parse($session->start_time);
        $totalMinutes = $startTime->diffInMinutes($endTime);

        $rentalCost = 0;

        // --- Logika Perhitungan Biaya Rental ---
        if ($session->type === 'package' && $session->package) {
            // 1. Biaya awal paket
            $rentalCost = $session->package->price;

            // 2. Tambahkan biaya dari perpanjangan paket (jika ada)
            $rentalCost += $session->extended_cost;

            // 3. Total batas durasi resmi (Durasi Paket Awal + Perpanjangan)
            $allowedDuration = $session->package->duration_minutes + $session->extended_minutes;

            // 4. Cek overtime jika waktu main riil melebihi batas durasi resmi
            if ($totalMinutes > $allowedDuration) {
                $extraMinutes = $totalMinutes - $allowedDuration;
                $extraBlocks = floor($extraMinutes / 15);
                $ratePerBlock = $session->console->hourly_rate / 4;
                $rentalCost += ($extraBlocks * $ratePerBlock);
            }
        } else {
            // Jika Open Play, dihitung per blok 15 menit
            $billableBlocks = floor($totalMinutes / 15);
            $ratePerBlock = $session->console->hourly_rate / 4;
            $rentalCost = $billableBlocks * $ratePerBlock;
        }

        // --- Hitung Total FnB & Gross Total ---
        $fnbCost = $session->orders->sum('subtotal');
        $grossTotal = $rentalCost + $fnbCost;

        // --- 🟢 PERHITUNGAN DURASI UNTUK SYARAT PROMO ---
        // Jika tipe paket, gunakan durasi resmi paket (+ perpanjangan jika ada).
        // Jika Open Play, gunakan durasi riil berjalan ($totalMinutes).
        $effectiveDurationForPromo = $totalMinutes;

        if ($session->type === 'package' && $session->package) {
            $packageDuration = $session->package->duration_minutes + $session->extended_minutes;
            // Gunakan durasi mana yang lebih besar (durasi paket atau durasi riil jika overtime)
            $effectiveDurationForPromo = max($packageDuration, $totalMinutes);
        }

        // --- LOGIKA PENGECEKAN & APLIKASI PROMO ---
        $discountAmount = 0;
        $appliedPromoId = null;

        if ($request->filled('promotion_id')) {
            $promo = \App\Models\Promotion::find($request->promotion_id);

            if ($promo && $promo->is_active) {
                // 1. Cek Masa Berlaku Tanggal Promo
                $today = now()->toDateString();
                if (($promo->start_date && $today < $promo->start_date) || ($promo->end_date && $today > $promo->end_date)) {
                    return redirect()->back()->with('error', 'Transaksi gagal! Promo yang dipilih sudah kadaluarsa.');
                }

                // 2. Syarat Minimal Durasi Sewa
                if ($promo->min_duration_minutes > 0 && $effectiveDurationForPromo < $promo->min_duration_minutes) {
                    return redirect()->back()->with('error', "Transaksi gagal! Promo ini membutuhkan minimal durasi sewa {$promo->min_duration_minutes} menit (Durasi sesi ini: {$effectiveDurationForPromo} mnt).");
                }

                // 3. Syarat Minimal Total Transaksi (Mengecek Gross Total Sewa + FnB)
                if ($promo->min_transaction_amount > 0 && $grossTotal < $promo->min_transaction_amount) {
                    return redirect()->back()->with('error', "Transaksi gagal! Promo ini membutuhkan minimal transaksi Rp " . number_format($promo->min_transaction_amount, 0, ',', '.') . " (Total transaksi saat ini: Rp " . number_format($grossTotal, 0, ',', '.') . ").");
                }

                // Hitung Potongan
                $appliedPromoId = $promo->id;
                if ($promo->type === 'discount_nominal') {
                    $discountAmount = $promo->discount_value;
                } elseif ($promo->type === 'discount_percent') {
                    $discountAmount = ($promo->discount_value / 100) * $grossTotal;
                } elseif ($promo->type === 'bonus_time') {
                    $discountAmount = 0; 
                }
            }
        }
        // Total Tagihan Akhir setelah dipotong Diskon
        $grandTotal = max(0, $grossTotal - $discountAmount);

        // --- Pembagian Nominal Cash & QRIS (Split Payment) ---
        $paymentMethod = $request->payment_method;
        $cashAmount = 0;
        $qrisAmount = 0;

        if ($paymentMethod === 'cash') {
            $cashAmount = $grandTotal;
            $qrisAmount = 0;
        } elseif ($paymentMethod === 'qris') {
            $cashAmount = 0;
            $qrisAmount = $grandTotal;
        } elseif ($paymentMethod === 'split') {
            $cashAmount = (float) $request->cash_amount;
            $qrisAmount = (float) $request->qris_amount;

            // Validasi: Jumlah gabungan Cash + QRIS tidak boleh kurang dari Total Biaya
            if (($cashAmount + $qrisAmount) < $grandTotal) {
                return redirect()->back()->with('error', 'Jumlah pembayaran (Cash + QRIS) kurang dari total tagihan (Rp ' . number_format($grandTotal, 0, ',', '.') . ')');
            }
        }

        // --- Simpan Perubahan Sesi Rental ---
        $session->update([
            'shift_id'        => $activeShift->id,
            'end_time'        => $endTime,
            'rental_cost'     => $rentalCost,
            'fnb_cost'        => $fnbCost,
            'promotion_id'    => $appliedPromoId,   // 👈 Simpan ID promo yang digunakan
            'discount_amount' => $discountAmount,  // 👈 Simpan nominal potongan harga
            'total_cost'      => $grandTotal,       // Total bersih setelah diskon
            'payment_method'  => $paymentMethod,
            'cash_amount'     => $cashAmount,
            'qris_amount'     => $qrisAmount,
            'status'          => 'completed',
            'notes'     => null,
        ]);

        $session->console->update(['status' => 'ready']);

        // Kembalikan ke dashboard dengan membawa ID sesi yang baru di-stop
        return redirect()->back()->with([
            'success'          => 'Sesi rental berhasil diselesaikan!',
            'show_receipt_id' => $session->id,
        ]);
    }
    public function addOrder(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $session = RentalSession::findOrFail($sessionId);
        $product = Product::findOrFail($request->product_id);

        // Cek ketersediaan stok
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi.');
        }

        // 🟢 1. Cek apakah produk ini SUDAH ADA di sesi aktif ini
        $existingOrder = Order::where('rental_session_id', $session->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingOrder) {
            // Jika sudah ada, cukup tambahkan quantity & subtotalnya
            $existingOrder->quantity += $request->quantity;
            $existingOrder->subtotal += ($product->price * $request->quantity);
            $existingOrder->save();
        } else {
            // Jika belum ada, buat record order baru
            Order::create([
                'rental_session_id' => $session->id,
                'product_id'        => $product->id,
                'quantity'          => $request->quantity,
                'price'             => $product->price,
                'subtotal'          => $product->price * $request->quantity,
            ]);
        }

        // Kurangi stok produk
        $product->decrement('stock', $request->quantity);

        return redirect()->back()->with('success', 'Berhasil menambahkan pesanan FnB.');
    }

    public function deleteOrder($id)
    {
        // 1. Cari data order beserta relasi produknya
        $order = Order::with('product')->findOrFail($id);

        // 2. Pastikan sesi rental-nya masih aktif (belum di-stop)
        if ($order->rentalSession && $order->rentalSession->status !== 'active') {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dihapus karena sesi rental sudah selesai.');
        }

        // 🟢 3. Kembalikan stok produk jika relasi produk ditemukan
        if ($order->product) {
            $order->product->increment('stock', $order->quantity);
        }

        // 4. Hapus data order dari database
        $order->delete();

        return redirect()->back()->with('success', 'Pesanan FnB berhasil dibatalkan dan stok telah dikembalikan.');
    }
    
    public function printReceipt($id)
    {
        // Ambil data sesi rental beserta relasi console, paket, dan orders FnB
        $session = RentalSession::with(['console', 'package', 'orders.product'])->findOrFail($id);

        return view('rental.receipt', compact('session'));
    }
    public function extendSession(Request $request, $sessionId)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $session = RentalSession::where('status', 'active')->findOrFail($sessionId);
        $package = Package::findOrFail($request->package_id);

        // Update durasi, biaya, dan nama paket perpanjangan
        $session->increment('extended_minutes', $package->duration_minutes);
        $session->increment('extended_cost', $package->price);
        
        // Simpan/gabungkan nama paket perpanjangan
        $currentExtendedName = $session->extended_package_name 
            ? $session->extended_package_name . ', ' . $package->name 
            : $package->name;

        $session->update([
            'extended_package_name' => $currentExtendedName
        ]);

        return redirect()->back()->with('success', 'Paket berhasil diperpanjang!');
    }

    public function updateNotes(Request $request, $id)
    {
        $session = \App\Models\RentalSession::findOrFail($id);
        
        $session->update([
            'notes' => $request->input('notes')
        ]);

        return redirect()->back()->with('success', 'Catatan berhasil diperbarui!');
    }
        
    public function transferConsole(Request $request, $id)
    {
        $request->validate([
            'new_console_id' => ['required', 'exists:consoles,id'],
        ]);

        $session = RentalSession::with('console')->findOrFail($id);
        $newConsole = Console::findOrFail($request->new_console_id);

        // 1. Validasi: Pastikan konsol tujuan tidak sama dengan konsol saat ini
        if ($session->console_id == $newConsole->id) {
            return redirect()->back()->with('error', 'Konsol tujuan tidak boleh sama dengan konsol saat ini!');
        }

        // 2. Validasi Fleksibel: Cek apakah konsol tujuan benar-benar sedang dipadakai sesi aktif
        $isTargetOccupied = $newConsole->sessions()->where('status', 'active')->exists();
        if ($isTargetOccupied) {
            return redirect()->back()->with('error', 'Konsol tujuan sedang digunakan oleh sesi lain!');
        }

        DB::transaction(function () use ($session, $newConsole) {
            $oldConsole = $session->console;

            // Update status konsol (jika kolom status digunakan di database)
            if (isset($oldConsole->status)) {
                $oldConsole->update(['status' => 'available']); // atau 'kosong' sesuai konvensi DB kamu
            }
            
            if (isset($newConsole->status)) {
                $newConsole->update(['status' => 'occupied']); // atau 'dipakai'
            }

            // Pindahkan ID konsol pada sesi rental yang berjalan
            $session->update([
                'console_id' => $newConsole->id,
            ]);
        });

        return redirect()->back()->with('success', "Sesi berhasil dipindahkan dari {$session->console->name} ke {$newConsole->name}!");
    }
}