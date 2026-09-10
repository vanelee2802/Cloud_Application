<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignNail extends Model
{
    protected $fillable = ['design_id', 'nail_position', 'nail_shape_id', 'color_id'];

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function nailShape()
    {
        return $this->belongsTo(NailShape::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function designElements()
    {
        return $this->belongsToMany(DesignElement::class, 'design_nail_elements');
    }
}