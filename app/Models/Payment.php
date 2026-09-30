<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class Payment extends Model
{
    protected $fillable = [
        'appointment_id',
        'amount',
        'status',
        'stripe_payment_id',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}