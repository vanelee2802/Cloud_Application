<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['nail_studio_id', 'name', 'price', 'duration_minutes'];

    public function nailStudio()
    {
        return $this->belongsTo(NailStudio::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}