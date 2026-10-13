<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentAlternative extends Model
{
    protected $fillable = [
        'appointment_id',
        'date',
        'time',
    ];

    // Der Termin, zu dem dieser Vorschlag gehört
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}