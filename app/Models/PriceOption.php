<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Preisbare Option ohne eigene Tabelle:
 * Effekte, Verzierungen, Längen, Materialien.
 *
 * category: effekt | verzierung | laenge | material
 * unit:     nagel (pro Nagel) | pauschal (einmal pro Termin)
 */
class PriceOption extends Model
{
    public const CATEGORIES = ['effekt', 'verzierung', 'laenge', 'material'];

    protected $fillable = [
        'category',
        'key',
        'name',
        'default_price',
        'unit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    /** Abweichende Preise einzelner Studios */
    public function studioPrices(): MorphMany
    {
        return $this->morphMany(StudioPrice::class, 'priceable');
    }
}