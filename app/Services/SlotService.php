<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;



class SlotService
{
    /**
     * Wie viele Termine pro Uhrzeit gleichzeitig möglich sind
     * = Anzahl Mitarbeiter dieses Studios.
     */
    public function capacity(int $studioId): int
    {
        return User::where('nail_studio_id', $studioId)
            ->whereIn('role', ['employee', 'admin'])
            ->count();
    }

        /**
     * Freie Plätze pro Uhrzeit an einem Tag,
     * z. B. [['time' => '14:00', 'free' => 2], ...]
     */
    public function availability(int $studioId, string $date): array
    {
        $capacity = $this->capacity($studioId);

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
                'free' => max(0, $capacity - ($taken[$time] ?? 0)),
            ])
            ->all();
    }

        /**
     * Reserviert einen freien Platz für 3 Minuten.
     * Gibt den Termin zurück oder null, wenn alles belegt ist.
     */
    public function hold(int $userId, array $data): ?Appointment
    {
        $capacity = $this->capacity($data['nail_studio_id']);

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
}