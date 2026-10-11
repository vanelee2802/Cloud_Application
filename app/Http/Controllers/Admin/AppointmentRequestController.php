<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;

/**
 * Studioleitung: Anfragen für Termine am selben Tag bearbeiten
 */
class AppointmentRequestController extends Controller
{
    // Alle offenen Anfragen, früheste zuerst
    public function index()
    {
        $requests = Appointment::with('service')
            ->where('status', 'requested')
            ->orderBy('date')
            ->orderBy('time')
            ->get([
                'id', 'service_id', 'date', 'time',
                'customer_name', 'customer_email', 'created_at',
            ]);

        return response()->json($requests);
    }

    // NEU: Anfrage bestätigen → Kundin kann bezahlen
    public function confirm(Appointment $appointment)
    {
        // Nur ändern, wenn die Anfrage JETZT noch offen ist
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'requested')
            ->update([
                'status' => 'awaiting_payment',
                'payment_expires_at' => now()->addMinutes(config('booking.same_day_payment_minutes')),
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Diese Anfrage wurde bereits bearbeitet.',
            ], 409);
        }

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->service->price,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Termin bestätigt. Die Kundin kann jetzt bezahlen.',
            'payment_id' => $payment->id,
        ]);
    }
        // NEU: Anfrage komplett absagen → Platz wird wieder frei
    public function decline(Appointment $appointment)
    {
        // Nur ändern, wenn die Anfrage JETZT noch offen ist
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'requested')
            ->update([
                'status' => 'rejected',
                'slot_key' => null,
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Diese Anfrage wurde bereits bearbeitet.',
            ], 409);
        }

        return response()->json([
            'message' => 'Termin abgesagt. Der Platz ist wieder frei.',
        ]);
    }
}