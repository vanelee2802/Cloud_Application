<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    
    protected $fillable = ['name', 'hex_code', 'category'];

    public function designNails()
    {
        return $this->hasMany(DesignNail::class);
    }
}