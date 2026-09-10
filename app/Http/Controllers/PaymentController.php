<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Zeigt alle Zahlungen (später v.a. für Admin-Dashboard)
    public function index()
    {
        return response()->json(Payment::with('appointment')->get());
    }

    // Legt einen Zahlungsdatensatz an (vorbereitend, bevor Stripe angebunden wird)
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

    // Aktualisiert den Zahlungsstatus (später vom Stripe-Webhook aufgerufen)
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
}