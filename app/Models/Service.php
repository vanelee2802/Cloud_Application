<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'nail_studio_id',
        'name',
        'category',
        'description',
        'price',
        'price_is_from',
        'duration_minutes',
        'is_addon',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'price_is_from'    => 'boolean',
            'duration_minutes' => 'integer',
            'is_addon'         => 'boolean',
            'is_popular'       => 'boolean',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer',
        ];
    }

    public function nailStudio(): BelongsTo
    {
        return $this->belongsTo(NailStudio::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Kann man diese Leistung als eigenen Termin buchen? Zusatzleistungen nicht. */
    public function isBookable(): bool
    {
        return $this->is_active && ! $this->is_addon && $this->duration_minutes > 0;
    }

    /** Einheitliche Daten für Leistungsseite und Preisverwaltung */
    public function toCatalogArray(): array
    {
        return [
            'id'               => $this->id,
            'nail_studio_id'   => $this->nail_studio_id,
            'name'             => $this->name,
            'category'         => $this->category,
            'description'      => $this->description,
            'price'            => (float) $this->price,
            'price_is_from'    => $this->price_is_from,
            'duration_minutes' => $this->duration_minutes,
            'is_addon'         => $this->is_addon,
            'is_popular'       => $this->is_popular,
            'is_active'        => $this->is_active,
            'sort_order'       => $this->sort_order,
            'bookable'         => $this->isBookable(),
        ];
    }
}
