<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\AppointmentConfirmed;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppointmentDeclined;

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

     // Anfrage bestätigen → Kundin kann bezahlen
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

        // NEU: Aktuellen Stand laden (Status und Frist wurden eben per Abfrage geändert)
        $appointment->refresh();

        // NEU: Kundin per E-Mail benachrichtigen
        if ($appointment->customer_email) {
            Notification::route('mail', $appointment->customer_email)
                ->notify(new AppointmentConfirmed($appointment));
        }

        return response()->json([
            'message' => 'Termin bestätigt. Die Kundin wurde per E-Mail benachrichtigt.',
            'payment_id' => $payment->id,
        ]);
    }

    // Anfrage komplett absagen → Platz wird wieder frei
    public function decline(Request $request, Appointment $appointment)
    {
        // NEU: optionale Nachricht der Studioleitung an die Kundin
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        // Nur ändern, wenn die Anfrage JETZT noch offen ist
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'requested')
            ->update([
                'status' => 'rejected',
                'slot_key' => null,
                'admin_note' => $validated['note'] ?? null,
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Diese Anfrage wurde bereits bearbeitet.',
            ], 409);
        }

        // NEU: Aktuellen Stand laden und Kundin benachrichtigen
        $appointment->refresh();

        if ($appointment->customer_email) {
            Notification::route('mail', $appointment->customer_email)
                ->notify(new AppointmentDeclined($appointment));
        }

        return response()->json([
            'message' => 'Termin abgesagt. Die Kundin wurde per E-Mail benachrichtigt.',
        ]);
    }  

    // NEU: Ersatztermine anbieten → Wunschtermin wird frei, Kundin wählt einen Vorschlag
    public function offerAlternatives(Request $request, Appointment $appointment, SlotService $slots)
    {
        $validated = $request->validate([
            'alternatives' => 'required|array|min:1|max:5',
            'alternatives.*.date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'alternatives.*.time' => ['required', Rule::in(config('booking.times'))],
            'note' => 'nullable|string|max:1000',
        ]);

        // Jeder Vorschlag muss in der Zukunft liegen und noch frei sein
        foreach ($validated['alternatives'] as $i => $alt) {
            $slot = collect($slots->availability($appointment->nail_studio_id, $alt['date']))
                ->firstWhere('time', $alt['time']);

            $inFuture = Carbon::parse("{$alt['date']} {$alt['time']}")->isFuture();

            if (! $slot || $slot['free'] === 0 || ! $inFuture) {
                throw ValidationException::withMessages([
                    "alternatives.$i" => "Der {$alt['date']} um {$alt['time']} Uhr ist nicht frei.",
                ]);
            }
        }

        // Alles oder nichts: Status ändern UND Vorschläge speichern
        $ok = DB::transaction(function () use ($appointment, $validated) {
            $updated = Appointment::whereKey($appointment->id)
                ->where('status', 'requested')
                ->update([
                    'status' => 'alternatives_offered',
                    'slot_key' => null,
                    'admin_note' => $validated['note'] ?? null,
                ]);

            if (! $updated) {
                return false;
            }

            $appointment->alternatives()->createMany($validated['alternatives']);

            return true;
        });

        if (! $ok) {
            return response()->json([
                'message' => 'Diese Anfrage wurde bereits bearbeitet.',
            ], 409);
        }

        return response()->json([
            'message' => 'Alternativen gesendet. Der Wunschtermin ist wieder frei.',
        ]);
    }
}