<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Events\AppointmentStatusChanged;
use Illuminate\Http\Request;
use App\Services\SlotService;
use Illuminate\Validation\Rule;
use App\Models\Payment;
use App\Services\StripeCheckoutService;
use App\Models\AppointmentAlternative;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use App\Models\User;                                   // NEU (Schritt 60)
use App\Notifications\AppointmentRequested;            // NEU (Schritt 60)
use Illuminate\Support\Facades\Notification;           // NEU (Schritt 60)
use App\Models\Design;                                 // NEU (Warenkorb-Buchung)
use App\Models\Service;                                // NEU (Warenkorb-Buchung)
use Illuminate\Validation\ValidationException;         // NEU (Warenkorb-Buchung)

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
            'date'           => 'required|date_format:Y-m-d|after_or_equal:today',

            // Optional: gewählte Dienstleistung → Dauer wird berücksichtigt
            'service_id'     => 'nullable|integer|exists:services,id',
        ]);

        $duration = null;

        // GEÄNDERT: Leistung muss zum Studio gehören und buchbar sein
        if (isset($validated['service_id'])) {
            $service = $this->bookableService(
                (int) $validated['service_id'],
                (int) $validated['nail_studio_id']
            );

            $duration = $service->duration_minutes;
        }

        return response()->json([
            'times' => $slots->availability(
                (int) $validated['nail_studio_id'],
                $validated['date'],
                $duration
            ),
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

        // NEU: Nur buchbare Leistungen dieses Studios
        $this->bookableService((int) $validated['service_id'], (int) $validated['nail_studio_id']);

        // NEU: Ein Design darf nur von seiner Besitzerin mitgebucht werden
        if (! empty($validated['design_id'])) {
            $this->ensureOwnDesign($request, (int) $validated['design_id']);
        }

        $appointment = $slots->hold($request->user()?->id, $validated);

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
    public function submit(Request $request, Appointment $appointment, StripeCheckoutService $checkout)
    {
        $this->authorizeHold($request, $appointment);

        $user = $request->user();

        // Gäste müssen Name und E-Mail angeben, eingeloggte Kundinnen nicht
        $validated = $request->validate([
            'customer_name' => [Rule::requiredIf(! $user), 'nullable', 'string', 'max:255'],
            'customer_email' => [Rule::requiredIf(! $user), 'nullable', 'email', 'max:255'],
        ]);

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
                // Kontaktdaten aus dem Konto oder aus dem Formular
                'customer_name' => $user?->name ?? $validated['customer_name'],
                'customer_email' => $user?->email ?? $validated['customer_email'],
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Deine Reservierung ist abgelaufen. Bitte wähle den Termin erneut.',
            ], 409);
        }

        // Heute: Anfrage an die Studioleitung, noch keine Zahlung
        if ($isToday) {
            // NEU (Schritt 60): Studioleitung dieses Studios per E-Mail benachrichtigen
            $adminEmails = User::where('role', 'admin')
                ->where('nail_studio_id', $appointment->nail_studio_id)
                ->pluck('email')
                ->all();

            if ($adminEmails) {
                Notification::route('mail', $adminEmails)
                    ->notify(new AppointmentRequested($appointment->refresh()));
            }

            return response()->json([
                'status' => $status,
            ]);
        }

        // Späterer Tag: Payment-Eintrag anlegen und direkt zu Stripe
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->service->price,
            'status' => 'pending',
        ]);

        try {
            $checkoutUrl = $checkout->createCheckoutUrl($payment);
        } catch (\Throwable $e) {
            // Stripe nicht erreichbar → Platz wieder freigeben, damit er nicht blockiert
            report($e);

            Appointment::whereKey($appointment->id)->update([
                'status' => 'expired',
                'slot_key' => null,
                'payment_expires_at' => null,
            ]);
            $payment->update(['status' => 'failed']);

            return response()->json([
                'message' => 'Die Zahlung konnte gerade nicht gestartet werden. Bitte versuche es in einer Minute erneut.',
            ], 502);
        }

        return response()->json([
            'status' => $status,
            'payment_id' => $payment->id,
            'checkout_url' => $checkoutUrl,
        ]);
    }

    // Kundin wählt einen der angebotenen Ersatztermine → direkt zur Zahlung
    public function acceptAlternative(
        Request $request,
        Appointment $appointment,
        AppointmentAlternative $alternative,
        SlotService $slots,
        StripeCheckoutService $checkout
    ) {
        $this->authorizeCustomer($request, $appointment);

        // Der Vorschlag muss zu genau diesem Termin gehören
        abort_unless((int) $alternative->appointment_id === (int) $appointment->id, 404);

        // Alten Stand merken, falls wir zurücksetzen müssen
        $oldDate = $appointment->date;
        $oldTime = $appointment->time;

        // 1. Nur umstellen, wenn JETZT noch kein Vorschlag gewählt wurde
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'alternatives_offered')
            ->update([
                'status' => 'awaiting_payment',
                'date' => $alternative->date,
                'time' => $alternative->time,
                'payment_expires_at' => now()->addMinutes(config('booking.same_day_payment_minutes')),
            ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Du hast bereits einen Termin gewählt.',
            ], 409);
        }

        $appointment->refresh();

        // 2. Platz für den neuen Termin holen
        if (! $slots->claimSeat($appointment)) {
            // Inzwischen voll → alles zurück, Kundin kann einen anderen Vorschlag wählen
            $appointment->update([
                'status' => 'alternatives_offered',
                'date' => $oldDate,
                'time' => $oldTime,
                'payment_expires_at' => null,
            ]);

            return response()->json([
                'message' => 'Dieser Termin ist inzwischen vergeben. Bitte wähle einen anderen Vorschlag.',
            ], 409);
        }

        // 3. Zahlung vorbereiten
        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->service->price,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'awaiting_payment',
            'payment_id' => $payment->id,
            'checkout_url' => $checkout->createCheckoutUrl($payment),
        ]);
    }

    // Bestätigten Termin bezahlen – eingeloggt oder per signiertem Link
    public function pay(Request $request, Appointment $appointment, StripeCheckoutService $checkout)
    {
        $this->authorizeCustomer($request, $appointment);

        // Die offene Zahlung zu diesem Termin
        $payment = $appointment->payment()
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $canPay = $payment
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

    // Terminseite aus der E-Mail – Gast per signiertem Link oder eingeloggte Besitzerin
    public function manage(Request $request, Appointment $appointment)
    {
        $this->authorizeCustomer($request, $appointment);

        $appointment->load('service', 'alternatives');

        // Signierte Adressen für die Buttons, 1 Tag gültig
        $validUntil = now()->addDay();

        return Inertia::render('BookingManage', [
            'appointment' => [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'time' => substr($appointment->time, 0, 5),
                'status' => $appointment->status,
                'service' => $appointment->service->name,
                'price' => $appointment->service->price,
                'admin_note' => $appointment->admin_note,
                'payment_expires_at' => $appointment->payment_expires_at?->toIso8601String(),
            ],

            'payUrl' => URL::temporarySignedRoute('appointments.pay', $validUntil, [
                'appointment' => $appointment->id,
            ]),

            'alternatives' => $appointment->alternatives->map(fn ($alt) => [
                'id' => $alt->id,
                'date' => $alt->date,
                'time' => $alt->time,
                'acceptUrl' => URL::temporarySignedRoute('appointments.alternatives.accept', $validUntil, [
                    'appointment' => $appointment->id,
                    'alternative' => $alt->id,
                ]),
            ]),
        ]);
    }

    // NEU: Leistung muss zum Studio gehören, sichtbar und keine Zusatzleistung sein
    private function bookableService(int $serviceId, int $studioId): Service
    {
        $service = Service::find($serviceId);

        if (! $service || (int) $service->nail_studio_id !== $studioId || ! $service->isBookable()) {
            throw ValidationException::withMessages([
                'service_id' => 'Diese Leistung kann in diesem Studio nicht gebucht werden.',
            ]);
        }

        return $service;
    }

    // NEU: Gäste können kein Design mitbuchen, Kundinnen nur ihr eigenes
    private function ensureOwnDesign(Request $request, int $designId): void
    {
        $user = $request->user();

        $isOwn = $user && Design::whereKey($designId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $isOwn) {
            throw ValidationException::withMessages([
                'design_id' => 'Dieses Design kann nicht mitgebucht werden. Bitte melde dich mit dem Konto an, in dem du es gespeichert hast.',
            ]);
        }
    }

    // Prüft: Darf diese Anfrage die Reservierung freigeben oder abschicken?
    private function authorizeHold(Request $request, Appointment $appointment): void
    {
        $token = (string) $request->header('X-Hold-Token');

        // Den geheimen Code braucht IMMER jeder, auch eingeloggte Kundinnen
        $tokenOk = $appointment->hold_token && hash_equals($appointment->hold_token, $token);

        // Gehört die Reservierung zu einem Konto, muss genau dieses eingeloggt sein
        $userOk = $appointment->user_id === null
            || ($request->user() && (int) $appointment->user_id === (int) $request->user()->id);

        abort_unless($tokenOk && $userOk, 403);
    }

    // Prüft: Signierter Link aus der E-Mail ODER eingeloggte Besitzerin des Termins?
    private function authorizeCustomer(Request $request, Appointment $appointment): void
    {
        if ($request->hasValidSignature()) {
            return;
        }

        $isOwner = $appointment->user_id !== null
            && $request->user()
            && (int) $appointment->user_id === (int) $request->user()->id;

        abort_unless($isOwner, 403);
    }
}