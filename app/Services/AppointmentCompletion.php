<?php

namespace App\Services;

use App\Models\Appointment;
use App\Notifications\ReviewRequested;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 * Termine abschließen und um eine Bewertung bitten.
 *
 * Abgeschlossen wird ein bezahlter Termin (status = confirmed):
 *   - automatisch, sobald seine Endzeit vorbei ist (Beginn + Dauer der Leistung)
 *   - oder sofort, wenn die Schichtleitung „Abgeschlossen“ klickt
 *
 * Danach bekommt die Kundin genau eine Danke-Mail mit Link zum Bewertungsformular.
 */
class AppointmentCompletion
{
    /** Höchstens alle 10 Minuten automatisch prüfen (auch ohne Cron) */
    private const CHECK_EVERY_SECONDS = 600;

    /**
     * Für Aufrufe aus normalen Seitenaufrufen: prüft nur, wenn die letzte Prüfung
     * mindestens 10 Minuten her ist. So klappt es auch ohne Cron auf dem Server.
     */
    public function runIfDue(): void
    {
        if (Cache::add('appointments:completion-check', true, self::CHECK_EVERY_SECONDS)) {
            $this->completeFinished();
        }
    }

    /** Alle bezahlten Termine abschließen, deren Endzeit vorbei ist. Gibt die Anzahl zurück. */
    public function completeFinished(): int
    {
        $count = 0;

        Appointment::with('service')
            ->where('status', 'confirmed')
            ->whereDate('date', '<=', now()->toDateString())
            ->chunkById(100, function ($appointments) use (&$count) {
                foreach ($appointments as $appointment) {
                    if ($this->endsAt($appointment)->lte(now()) && $this->complete($appointment)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    /** Einen Termin abschließen. false, wenn er nicht (mehr) bezahlt-offen war. */
    public function complete(Appointment $appointment): bool
    {
        // Nur umstellen, wenn er JETZT noch offen ist – verhindert doppelte Mails
        $updated = Appointment::whereKey($appointment->id)
            ->where('status', 'confirmed')
            ->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

        if (! $updated) {
            return false;
        }

        $this->sendReviewRequest($appointment->refresh());

        return true;
    }

    public function startsAt(Appointment $appointment): Carbon
    {
        $date = substr((string) $appointment->getRawOriginal('date'), 0, 10);
        $time = substr((string) $appointment->getRawOriginal('time'), 0, 5);

        return Carbon::parse("{$date} {$time}");
    }

    public function endsAt(Appointment $appointment): Carbon
    {
        $appointment->loadMissing('service');

        return $this->startsAt($appointment)
            ->addMinutes((int) ($appointment->service?->duration_minutes ?? 0));
    }

    private function sendReviewRequest(Appointment $appointment): void
    {
        if ($appointment->review_requested_at) {
            return;
        }

        $email = $appointment->customer_email ?: $appointment->user?->email;

        if (blank($email)) {
            return;
        }

        try {
            Notification::route('mail', $email)->notify(new ReviewRequested($appointment));

            Appointment::whereKey($appointment->id)->update(['review_requested_at' => now()]);
        } catch (\Throwable $e) {
            // Mail-Fehler sollen das Abschließen nicht verhindern
            report($e);
        }
    }
}
