<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    // Relasi: 1 Produk bisa diorder di banyak transaksi
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}