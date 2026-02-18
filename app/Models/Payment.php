<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id','amount','status'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => 'string',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
