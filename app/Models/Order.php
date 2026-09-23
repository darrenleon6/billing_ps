<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    // Relasi: Order ini milik 1 Sesi Rental
    public function rentalSession()
    {
        return $this->belongsTo(RentalSession::class);
    }

    // Relasi: Order ini merujuk ke 1 Produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}