<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'starting_cash',
        'expected_cash',
        'actual_cash',
        'difference_cash',
        'total_qris',
        'status',
        'notes',
    ];

   public function user()
    {
        return $this->belongsTo(User::class); // 🟢 Gunakan $this, bukan $table
    }

    public function sessions()
    {
        return $this->hasMany(RentalSession::class); // 🟢 Gunakan $this
    }
}