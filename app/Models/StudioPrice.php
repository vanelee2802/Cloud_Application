<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * "Studio X verlangt für Y den Preis Z."
 * Y ist entweder ein Muster (DesignElement) oder eine PriceOption.
 * Gibt es keinen Eintrag, gilt der Standardpreis.
 */
class StudioPrice extends Model
{
    protected $fillable = [
        'nail_studio_id',
        'priceable_type',
        'priceable_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function nailStudio(): BelongsTo
    {
        return $this->belongsTo(NailStudio::class);
    }

    /** Das Muster bzw. die Option, um die es geht */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }
}
