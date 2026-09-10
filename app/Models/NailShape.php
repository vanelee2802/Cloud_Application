<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NailShape extends Model
{
    protected $fillable = ['name'];

    public function designNails()
    {
        return $this->hasMany(DesignNail::class);
    }
}