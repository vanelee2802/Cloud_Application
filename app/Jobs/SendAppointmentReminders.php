<?php

namespace App\Jobs;

use App\Models\Appointment;
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
            $alreadyReminded = $appointment->user->notifications()
                ->where('type', 'appointment_reminder')
                ->where('message', 'like', '%#' . $appointment->id . '%')
                ->exists();

            if (!$alreadyReminded) {
                $appointment->user->notifications()->create([
                    'type' => 'appointment_reminder',
                    'message' => 'Erinnerung: Dein Termin morgen um ' . $appointment->time . ' Uhr steht an (#' . $appointment->id . ').',
                    'read' => false,
                ]);
            }
        }
    }
}