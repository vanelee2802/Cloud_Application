<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Events\AppointmentStatusChanged;
use Illuminate\Http\Request;
use App\Services\SlotService;
use Illuminate\Validation\Rule;
use App\Models\Payment;
class AppointmentController extends Controller
{
    // Zeigt Termine: Kunden sehen ihre eigenen, Mitarbeiter/Admins sehen alle
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Appointment::with('service', 'nailStudio', 'design', 'employee', 'user');

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    // Zeigt einen einzelnen Termin
    public function show(string $id)
    {
        $appointment = Appointment::with('service', 'nailStudio', 'design', 'employee', 'user', 'payment')
            ->findOrFail($id);

        return response()->json($appointment);
    }

    // Mitarbeiter bestätigt/lehnt ab, oder weist sich selbst zu
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:requested,confirmed,rejected,completed',
            'employee_id' => 'sometimes|exists:users,id',
        ]);

        $oldStatus = $appointment->status;
        $appointment->update($validated);

        // Wenn sich der Status geändert hat: Kunden benachrichtigen
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $messages = [
                'confirmed' => 'Dein Termin wurde bestätigt.',
                'rejected' => 'Dein Termin wurde leider abgelehnt.',
                'completed' => 'Dein Termin wurde als abgeschlossen markiert.',
            ];

            if (isset($messages[$validated['status']])) {
                $appointment->user->notifications()->create([
                    'type' => 'appointment_status_changed',
                    'message' => $messages[$validated['status']],
                    'read' => false,
                ]);

                broadcast(new AppointmentStatusChanged($appointment));
            }
        }

        return response()->json($appointment->load('service', 'nailStudio', 'employee'));
    }

    // Freie Plätze pro Uhrzeit an einem Tag
    public function availability(Request $request, SlotService $slots)
    {
        $validated = $request->validate([
            'nail_studio_id' => 'required|integer|exists:nail_studios,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        return response()->json([
            'times' => $slots->availability((int) $validated['nail_studio_id'], $validated['date']),
        ]);
    }

    // Kundin wählt eine Uhrzeit → Platz für 3 Minuten reservieren
    public function hold(Request $request, SlotService $slots)
    {
        $validated = $request->validate([
            'nail_studio_id' => 'required|integer|exists:nail_studios,id',
            'service_id' => 'required|integer|exists:services,id',
            'design_id' => 'nullable|integer|exists:designs,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => ['required', Rule::in(config('booking.times'))],
        ]);

        $appointment = $slots->hold($request->user()->id, $validated);

        if (! $appointment) {
            return response()->json([
                'message' => 'Diese Uhrzeit ist leider schon ausgebucht.',
            ], 422);
        }

        return response()->json([
            'id' => $appointment->id,
            'hold_token' => $appointment->hold_token,
            'expires_in' => config('booking.hold_minutes') * 60,
        ], 201);
    }

    // Kundin wählt andere Uhrzeit oder schließt den Checkout → Platz freigeben
    public function releaseHold(Request $request, Appointment $appointment)
    {
        $this->authorizeHold($request, $appointment);

        if ($appointment->status === 'held') {
            $appointment->update([
                'status' => 'cancelled',
                'slot_key' => null,
                'hold_token' => null,
                'hold_expires_at' => null,
            ]);
        }

        return response()->noContent();
    }

    // Kundin schickt den reservierten Termin ab
       // Kundin schickt den reservierten Termin ab
    public function submit(Request $request, Appointment $appointment)
    {
        $this->authorizeHold($request, $appointment);

        $isToday = $appointment->date === now()->toDateString();
        $status = $isToday ? 'requested' : 'awaiting_payment';

        // Nur ändern, wenn die Reservierung JETZT noch gültig ist
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'held')
            ->where('hold_expires_at', '>', now())
            ->update([
                'status' => $status,
                'hold_token' => null,
                'hold_expires_at' => null,
                // Heute: noch keine Frist, erst nach Bestätigung durch die Studioleitung
                'payment_expires_at' => $isToday ? null : now()->addMinutes(config('booking.payment_minutes')),
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Deine Reservierung ist abgelaufen. Bitte wähle den Termin erneut.',
            ], 409);
        }

        // Späterer Tag: Payment-Eintrag für deine bestehende checkout-Methode anlegen
        $payment = null;

        if (! $isToday) {
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $appointment->service->price,
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'status' => $status,
            'payment_id' => $payment?->id,
        ]);
    }

    // Prüft: Gehört die Reservierung dem eingeloggten Kunden UND stimmt der Token?
    private function authorizeHold(Request $request, Appointment $appointment): void
    {
        $token = (string) $request->header('X-Hold-Token');

        $isOwner = (int) $appointment->user_id === (int) $request->user()->id;
        $tokenOk = $appointment->hold_token && hash_equals($appointment->hold_token, $token);

        abort_unless($isOwner && $tokenOk, 403);
    }
}