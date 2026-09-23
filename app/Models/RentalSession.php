<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalSession extends Model
{
    protected $guarded = [];

    // Relasi: Sesi ini milik 1 Console
    public function console()
    {
        return $this->belongsTo(Console::class);
    }

    // Relasi: 1 Sesi rental bisa punya banyak Order FnB
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
