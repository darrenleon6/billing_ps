<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    protected $guarded = [];

    // Relasi: 1 Console punya banyak sesi rental
    public function rentalSessions()
    {
        return $table = $this->hasMany(RentalSession::class);
    }
}
