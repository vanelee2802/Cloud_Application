<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
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

        if ($paymentId) {
            $payment = Payment::find($paymentId);

            if ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'stripe_payment_id' => $session->payment_intent,
                ]);
            }
        }
    }

    return response()->json([
        'message' => 'Webhook received',
    ]);
}
}