<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignNailElement extends Model
{
    protected $fillable = ['design_nail_id', 'design_element_id'];

    public function designNail()
    {
        return $this->belongsTo(DesignNail::class);
    }

    public function designElement()
    {
        return $this->belongsTo(DesignElement::class);
    }
}