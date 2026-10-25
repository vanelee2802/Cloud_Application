<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\EmployeeShift;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SlotService
{
    /** Merkt sich pro Anfrage, ob ein Studio Schichten benutzt */
    private array $usesShifts = [];

    /**
     * Wie viele Termine zu einem Zeitpunkt gleichzeitig möglich sind.
     *
     * Mit Datum + Uhrzeit:
     *   Anzahl der Personen, die an diesem Wochentag eine Schicht haben,
     *   die den ganzen Termin (Uhrzeit + Dauer) abdeckt.
     *
     * Ohne Datum/Uhrzeit oder solange im Studio noch niemand Schichten
     * eingetragen hat: wie bisher alle Mitarbeiterinnen + Schichtleitungen.
     */
    public function capacity(int $studioId, ?string $date = null, ?string $time = null, ?int $durationMinutes = null): int
    {
        $staff = User::where('nail_studio_id', $studioId)
            ->whereIn('role', [User::ROLE_EMPLOYEE, User::ROLE_ADMIN]);

        if ($date === null || $time === null || ! $this->studioUsesShifts($studioId)) {
            return $staff->count();
        }

        $weekday = Carbon::parse($date)->dayOfWeekIso;          // 1 = Montag … 7 = Sonntag
        $start   = Carbon::createFromFormat('H:i', substr($time, 0, 5));
        $end     = $start->copy()->addMinutes(max(0, (int) $durationMinutes));

        return $staff
            ->whereHas('shifts', function ($query) use ($weekday, $start, $end, $durationMinutes) {
                $query->where('weekday', $weekday)
                    ->whereTime('start_time', '<=', $start->format('H:i:s'));

                // Mit Dauer: Schicht muss bis zum Terminende gehen.
                // Ohne Dauer: Schicht muss zum Terminbeginn noch laufen.
                $durationMinutes
                    ? $query->whereTime('end_time', '>=', $end->format('H:i:s'))
                    : $query->whereTime('end_time', '>', $start->format('H:i:s'));
            })
            ->count();
    }

    /** Hat irgendwer in diesem Studio schon Schichten eingetragen? */
    private function studioUsesShifts(int $studioId): bool
    {
        return $this->usesShifts[$studioId] ??= EmployeeShift::whereHas('user', function ($query) use ($studioId) {
            $query->where('nail_studio_id', $studioId)
                ->whereIn('role', [User::ROLE_EMPLOYEE, User::ROLE_ADMIN]);
        })->exists();
    }

    /** Dauer einer Dienstleistung in Minuten (null, wenn unbekannt) */
    private function durationOf(?int $serviceId): ?int
    {
        return $serviceId ? Service::whereKey($serviceId)->value('duration_minutes') : null;
    }


    /**
     * Gibt Plätze frei, deren Frist abgelaufen ist:
     * 3-Minuten-Reservierungen und nicht bezahlte Termine.
     */
    public function releaseExpired(): void
    {
        // 1. Abgelaufene 3-Minuten-Reservierungen
        Appointment::where('status', 'held')
            ->where('hold_expires_at', '<', now())
            ->update([
                'status' => 'expired',
                'slot_key' => null,
                'hold_expires_at' => null,
            ]);

        // 2. Nicht rechtzeitig bezahlte Termine
        $unpaidIds = Appointment::where('status', 'awaiting_payment')
            ->where('payment_expires_at', '<', now())
            ->pluck('id');

        if ($unpaidIds->isNotEmpty()) {
            Appointment::whereIn('id', $unpaidIds)->update([
                'status' => 'expired',
                'slot_key' => null,
                'payment_expires_at' => null,
            ]);

            Payment::whereIn('appointment_id', $unpaidIds)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }
    }

    /**
     * Freie Plätze pro Uhrzeit an einem Tag,
     * z. B. [['time' => '14:00', 'free' => 2], ...]
     *
     * $durationMinutes (optional): Dauer der gewählten Dienstleistung.
     * Dann zählen nur Personen, deren Schicht den ganzen Termin abdeckt.
     */
    public function availability(int $studioId, string $date, ?int $durationMinutes = null): array
    {
        $this->releaseExpired();

        // Wie viele Plätze sind pro Uhrzeit schon belegt?
        $taken = Appointment::where('nail_studio_id', $studioId)
            ->where('date', $date)
            ->whereNotNull('slot_key')
            ->pluck('time')                          // ["14:00:00", "14:00:00", "15:00:00"]
            ->map(fn ($time) => substr($time, 0, 5)) // ["14:00", "14:00", "15:00"]
            ->countBy();                             // ["14:00" => 2, "15:00" => 1]

        return collect(config('booking.times'))
            ->map(fn ($time) => [
                'time' => $time,
                'free' => max(0, $this->capacity($studioId, $date, $time, $durationMinutes) - ($taken[$time] ?? 0)),
            ])
            ->all();
    }

    /**
     * Reserviert einen freien Platz für 3 Minuten.
     * Gibt den Termin zurück oder null, wenn alles belegt ist.
     */
    public function hold(?int $userId, array $data): ?Appointment
    {
        $this->releaseExpired();

        $capacity = $this->capacity(
            $data['nail_studio_id'],
            $data['date'],
            $data['time'],
            $this->durationOf($data['service_id'] ?? null)
        );

        for ($seat = 1; $seat <= $capacity; $seat++) {
            try {
                return Appointment::create([
                    'user_id' => $userId,
                    'nail_studio_id' => $data['nail_studio_id'],
                    'service_id' => $data['service_id'],
                    'design_id' => $data['design_id'] ?? null,
                    'date' => $data['date'],
                    'time' => $data['time'],
                    'status' => 'held',
                    'slot_key' => Appointment::slotKey($data['nail_studio_id'], $data['date'], $data['time'], $seat),
                    'hold_token' => Str::random(40),
                    'hold_expires_at' => now()->addMinutes(config('booking.hold_minutes')),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Dieser Platz ist schon belegt → nächsten probieren
            }
        }

        return null; // alle Plätze belegt
    }

    /**
     * Gibt einem bestehenden Termin wieder einen freien Platz,
     * z. B. wenn die Zahlung knapp nach Ablauf der Frist eintrifft.
     * true = Platz bekommen, false = alles belegt.
     */
    public function claimSeat(Appointment $appointment): bool
    {
        $this->releaseExpired();

        $capacity = $this->capacity(
            $appointment->nail_studio_id,
            Carbon::parse($appointment->date)->toDateString(),
            substr($appointment->time, 0, 5),
            $this->durationOf($appointment->service_id)
        );

        for ($seat = 1; $seat <= $capacity; $seat++) {
            try {
                $appointment->update([
                    'slot_key' => Appointment::slotKey(
                        $appointment->nail_studio_id,
                        $appointment->date,
                        $appointment->time,
                        $seat
                    ),
                ]);

                return true;
            } catch (UniqueConstraintViolationException) {
                // Dieser Platz ist belegt → nächsten probieren
            }
        }

        // Alles belegt: den zuletzt probierten Wert im Speicher zurücksetzen
        $appointment->slot_key = null;

        return false;
    }
}
