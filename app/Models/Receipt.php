<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'number', 'year', 'sequence',
        'payment_id', 'appointment_id', 'nail_studio_id', 'user_id',
        'customer_name', 'customer_email',
        'studio_name', 'studio_address', 'tax_number', 'vat_id', 'is_small_business',
        'items',
        'vat_rate', 'net_cents', 'vat_cents', 'gross_cents',
        'service_date', 'issued_at',
    ];

    protected $casts = [
        'items'             => 'array',
        'is_small_business' => 'boolean',
        'service_date'      => 'date',
        'issued_at'         => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function nailStudio(): BelongsTo
    {
        return $this->belongsTo(NailStudio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Betrag in Cent als deutsche Euro-Angabe, z. B. 4990 → "49,90 €" */
    public static function euro(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.') . ' €';
    }
}