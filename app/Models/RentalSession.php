<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalSession extends Model
{
    protected $guarded = ['id'];

    // Relasi: Sesi ini milik 1 Console
    public function console()
    {
        return $this->belongsTo(Console::class);
    }

    /**
     * Relasi ke Model Package (Paket Rental)
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Relasi: 1 Sesi rental bisa punya banyak Order FnB
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
