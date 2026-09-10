<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 'nail_studio_id', 'service_id', 'employee_id',
        'design_id', 'date', 'time', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nailStudio()
    {
        return $this->belongsTo(NailStudio::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}