<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    protected $fillable = ['user_id', 'name', 'status', 'total_price'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nails()
    {
        return $this->hasMany(DesignNail::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}