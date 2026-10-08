<?php

namespace App\Jobs;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAppointmentReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $appointments = Appointment::where('status', 'confirmed')
            ->whereDate('date', now()->addDay()->toDateString())
            ->get();

        foreach ($appointments as $appointment) {
            // Mit Klammern suchen, damit (#1) nicht auch (#12) findet
            $alreadyReminded = $appointment->user->notifications()
                ->where('type', 'appointment_reminder')
                ->where('message', 'like', '%(#' . $appointment->id . ')%')
                ->exists();

            if (!$alreadyReminded) {
                // PostgreSQL liefert 14:00:00, wir wollen 14:00
                $uhrzeit = Carbon::parse($appointment->time)->format('H:i');

                $appointment->user->notifications()->create([
                    'type' => 'appointment_reminder',
                    'message' => 'Erinnerung: Dein Termin morgen um ' . $uhrzeit . ' Uhr steht an (#' . $appointment->id . ').',
                    'read' => false,
                ]);
            }
        }
    }
}