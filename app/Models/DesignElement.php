<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignElement extends Model
{
    protected $fillable = ['category', 'name', 'price_per_nail'];

    public function designNails()
    {
        return $this->belongsToMany(DesignNail::class, 'design_nail_elements');
    }
}