<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, SlotService $slots)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json([
                'error' => 'Invalid payload',
            ], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json([
                'error' => 'Invalid signature',
            ], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $paymentId = $session->metadata->payment_id ?? null;
            $payment = $paymentId ? Payment::find($paymentId) : null;

            // Nur verarbeiten, wenn die Zahlung existiert und noch nicht verbucht ist
            // (Stripe schickt Webhooks manchmal doppelt)
            if ($payment && $payment->status !== 'paid') {
                $appointment = $payment->appointment;

                // NEU: Sonderfall – die Frist war abgelaufen, der Platz schon freigegeben.
                // Erst versuchen, wieder einen Platz zu bekommen.
                if ($appointment->slot_key === null && ! $slots->claimSeat($appointment)) {

                    // Alles belegt → Geld automatisch zurück
                    (new StripeClient(config('services.stripe.secret')))->refunds->create([
                        'payment_intent' => $session->payment_intent,
                    ]);

                    // 'failed', weil die payments-Tabelle keinen Status 'refunded' kennt
                    $payment->update([
                        'status' => 'failed',
                        'stripe_payment_id' => $session->payment_intent,
                    ]);

                    $appointment->update([
                        'status' => 'cancelled',
                        'payment_expires_at' => null,
                    ]);

                    return response()->json(['message' => 'Refunded, no seat left']);
                }

                $payment->update([
                    'status' => 'paid',
                    'stripe_payment_id' => $session->payment_intent,
                ]);

                // Termin ist jetzt fest gebucht
                $appointment->update([
                    'status' => 'confirmed',
                    'payment_expires_at' => null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Webhook received',
        ]);
    }
}