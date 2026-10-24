<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NailStudio extends Model
{
    protected $fillable = [
        'name',
        'address',
        'owner_id',
        'opening_hours',

        // Steuerdaten für Rechnungen
        'tax_number',
        'vat_id',
        'is_small_business',
        'vat_rate',
    ];

    protected $casts = [
        'is_small_business' => 'boolean',
        'vat_rate'          => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function employees()
    {
        return $this->hasMany(User::class);
    }

    /** Inhaberin des Studios */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Sind die Steuerdaten vollständig genug für eine Rechnung?
     * Nach § 14 UStG braucht es Steuernummer ODER USt-IdNr.
     */
    public function hasTaxData(): bool
    {
        return filled($this->tax_number) || filled($this->vat_id);
    }
}