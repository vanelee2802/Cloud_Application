<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Services\StripeCheckoutService;

class PaymentController extends Controller
{
    // Zeigt alle Zahlungen (später v.a. für Admin-Dashboard)
    public function index()
    {
        return response()->json(Payment::with('appointment')->get());
    }

    // Legt einen Zahlungsdatensatz an
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $payment = Payment::create([
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json($payment, 201);
    }

    public function show(string $id)
    {
        return response()->json(Payment::with('appointment')->findOrFail($id));
    }

    // Aktualisiert den Zahlungsstatus
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,paid,failed',
            'stripe_payment_id' => 'sometimes|string',
        ]);

        $payment->update($validated);

        return response()->json($payment);
    }

    public function destroy(string $id)
    {
        Payment::findOrFail($id)->delete();

        return response()->json(['message' => 'Zahlung gelöscht']);
    }
    // Erstellt eine Stripe-Checkout-Session für eine bestehende Zahlung
    public function checkout(Request $request, string $id, StripeCheckoutService $checkout)
    {
        $payment = Payment::with('appointment')->findOrFail($id);
        $appointment = $payment->appointment;

        // Nur die Kundin, der der Termin gehört, darf bezahlen
        abort_unless((int) $appointment->user_id === (int) $request->user()->id, 403);

        // Kann der Termin noch bezahlt werden?
        $canPay = $payment->status === 'pending'
            && $appointment->status === 'awaiting_payment'
            && $appointment->payment_expires_at
            && $appointment->payment_expires_at->isFuture();

        if (! $canPay) {
            return response()->json([
                'message' => 'Dieser Termin kann nicht mehr bezahlt werden. Bitte buche neu.',
            ], 409);
        }

        return response()->json([
            'checkout_url' => $checkout->createCheckoutUrl($payment),
        ]);
    } 

    // Wird von Stripe automatisch aufgerufen, wenn sich eine Zahlung ändert
public function webhook(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );
    } catch (\Exception $e) {
        return response()->json(['error' => 'Ungültiger Webhook'], 400);
    }

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $payment = Payment::where('stripe_payment_id', $session->id)->first();

        if ($payment) {
            $payment->update(['status' => 'paid']);
        }
    }

    return response()->json(['received' => true]);
}
}