<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Vorgefertigtes Design (Vorlage) für den Design-Editor.
 */
class DesignTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'nails',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'nails'     => 'array',
        'is_active' => 'boolean',
    ];

    /** Tags zum Filtern und Suchen (dieselben wie bei den Mustern) */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}