<?php

namespace App\Services;

use App\Models\Payment;
use Stripe\StripeClient;

class StripeCheckoutService
{
    /**
     * Erstellt eine Stripe-Zahlungsseite für einen Payment-Eintrag
     * und gibt deren Link zurück.
     */
    public function createCheckoutUrl(Payment $payment): string
    {
        $appointment = $payment->appointment;

        // Stripe verlangt mindestens 30 Minuten Laufzeit
        $sessionExpiresAt = now()->addMinutes(31);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Nagelstudio-Termin #' . $payment->appointment_id,
                    ],
                    'unit_amount' => (int) round($payment->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',

            // Damit der Webhook die Zahlung wiederfindet
            'metadata' => ['payment_id' => $payment->id],

            // Stripe-Seite läuft ab, statt 24 Stunden offen zu bleiben
            'expires_at' => $sessionExpiresAt->timestamp,

                       'success_url' => route('booking.success'),
            'cancel_url' => route('booking.cancelled'),
        ]);

        $payment->update(['stripe_payment_id' => $session->id]);

        // Platz bleibt belegt, solange die Stripe-Seite offen ist
        if ($appointment->payment_expires_at->lt($sessionExpiresAt)) {
            $appointment->update(['payment_expires_at' => $sessionExpiresAt]);
        }

        return $session->url;
    }
}