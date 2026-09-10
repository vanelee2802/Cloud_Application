<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NailStudio extends Model
{
    protected $fillable = ['name', 'address', 'opening_hours'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function employees()
    {
        return $this->hasMany(User::class);
    }
}
