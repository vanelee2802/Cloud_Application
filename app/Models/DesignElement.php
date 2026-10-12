<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DesignElement extends Model
{
        protected $fillable = [
        'nail_studio_id',
        'category',
        'name',
        'price_per_nail',
        'image_path',
        'layers',
        'is_active',
    ];
       protected $casts = [
           'price_per_nail' => 'decimal:2',
           'is_active'      => 'boolean',
           'layers'         => 'array',
       ];


    // ---------------------------------------------
    // BEZIEHUNGEN
    // ---------------------------------------------

    // Bestehend: Nägel gebuchter Designs, die dieses Element nutzen
    public function designNails()
    {
        return $this->belongsToMany(DesignNail::class, 'design_nail_elements');
    }

    // Neu: Studio, dem das Muster gehört (NULL = Standard-Muster)
    public function nailStudio(): BelongsTo
    {
        return $this->belongsTo(NailStudio::class);
    }

    // Neu: Tags für die Suche
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }


    // ---------------------------------------------
    // ABFRAGE-HELFER
    // ---------------------------------------------

       // Nur Muster: mit Bild ODER mit Farbschichten
    public function scopePatterns(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotNull('image_path')
              ->orWhereNotNull('layers');
        });
    }

    // Nur sichtbare Elemente: DesignElement::active()->get()
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}