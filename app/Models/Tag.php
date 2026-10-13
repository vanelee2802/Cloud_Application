<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    // Alle Muster/Elemente, die diesen Tag haben
    public function designElements(): BelongsToMany
    {
        return $this->belongsToMany(DesignElement::class);
    }
}