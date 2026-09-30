<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'console_id',
        'package_id',
        'shift_id',
        'promotion_id',     // 👈 Pastikan promotion_id ada di $fillable
        'discount_amount',  // 👈 Pastikan discount_amount ada di $fillable
        'start_time',
        'end_time',
        'type',
        'status',
        'rental_cost',
        'fnb_cost',
        'total_cost',
        'payment_method',
        'cash_amount',
        'qris_amount',
        'extended_minutes',
        'extended_cost',
    ];

    // --- RELASI KE MODEL PROMOTION ---
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    // --- RELASI LAINNYA (SESUAIKAN DENGAN KODE KAMU) ---
    public function console()
    {
        return $this->belongsTo(Console::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}