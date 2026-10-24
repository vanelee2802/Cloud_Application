<?php

namespace App\Models;

use App\Services\ReceiptService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'appointment_id',
        'amount',
        'status',
        'stripe_payment_id',
    ];

    /**
     * Sobald eine Zahlung auf 'paid' wechselt, wird automatisch
     * die Rechnung ausgestellt – egal über welchen Weg
     * (Stripe-Webhook oder manuell im Admin-Bereich).
     */
    protected static function booted(): void
    {
        static::saved(function (Payment $payment) {
            if ($payment->status === 'paid' && $payment->wasChanged('status')) {
                app(ReceiptService::class)->issueFor($payment);
            }

            if ($payment->status === 'paid' && $payment->wasRecentlyCreated) {
                app(ReceiptService::class)->issueFor($payment);
            }
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }
}