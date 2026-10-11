<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Notifications\AlternativesOffered;
use App\Notifications\AppointmentConfirmed;
use App\Notifications\AppointmentDeclined;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        // Aktuellen Stand laden (Status und Frist wurden eben per Abfrage geändert)
        $appointment->refresh();

        // Kundin benachrichtigen: E-Mail und, falls sie ein Konto hat, im Kundenportal
        $appointment->notifyCustomer(
            new AppointmentConfirmed($appointment),
            'Dein Termin ist bestätigt. Bitte bezahle jetzt, damit er fest gebucht ist.'
        );

        return response()->json([
            'message' => 'Termin bestätigt. Die Kundin wurde benachrichtigt.',
            'payment_id' => $payment->id,
        ]);
    }

    // Anfrage komplett absagen → Platz wird wieder frei
    public function decline(Request $request, Appointment $appointment)
    {
        // Optionale Nachricht der Studioleitung an die Kundin
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

        $appointment->refresh();

        // GEÄNDERT (Schritt 63): E-Mail und Kundenportal
        $appointment->notifyCustomer(
            new AppointmentDeclined($appointment),
            'Dein Terminwunsch konnte leider nicht angenommen werden.'
        );

        return response()->json([
            'message' => 'Termin abgesagt. Die Kundin wurde benachrichtigt.',
        ]);
    }

    // Ersatztermine anbieten → Wunschtermin wird frei, Kundin wählt einen Vorschlag
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

        // Aktuellen Stand samt Vorschlägen laden
        $appointment->refresh()->load('alternatives');

        // GEÄNDERT (Schritt 63): E-Mail und Kundenportal
        $appointment->notifyCustomer(
            new AlternativesOffered($appointment),
            'Dein Wunschtermin ist leider belegt. Wähle jetzt einen der angebotenen Ersatztermine.'
        );

        return response()->json([
            'message' => 'Alternativen gesendet. Die Kundin wurde benachrichtigt.',
        ]);
    }
        // Gebuchte (bezahlte) Termine ab heute, nächste zuerst
    public function bookings()
    {
        $bookings = Appointment::with('service', 'user:id,name')
            ->where('status', 'confirmed')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('time')
            ->get([
                'id', 'service_id', 'user_id', 'date', 'time',
                'customer_name', 'customer_email', 'status',
            ]);

        return response()->json($bookings);
    }
}
