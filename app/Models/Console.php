<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi One-to-Many: 1 Console memiliki banyak RentalSession
     */
    public function sessions()
    {
        return $this->hasMany(RentalSession::class);
    }
}