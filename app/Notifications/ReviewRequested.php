<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Danke-Mail nach dem Termin mit Link zum Bewertungsformular (30 Tage gültig).
 */
class ReviewRequested extends Notification
{
    public function __construct(public Appointment $appointment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing('service', 'nailStudio');

        $url = URL::temporarySignedRoute('reviews.create', now()->addDays(30), [
            'appointment' => $appointment->id,
        ]);

        $greeting = filled($appointment->customer_name)
            ? "Hallo {$appointment->customer_name},"
            : 'Hallo,';

        return (new MailMessage)
            ->subject("Danke für deinen Besuch bei {$appointment->nailStudio->name}")
            ->greeting($greeting)
            ->line('danke, dass du bei uns warst! Wir hoffen, deine Nägel gefallen dir.')
            ->line("Wie war dein Termin „{$appointment->service->name}“? Deine Bewertung dauert nur eine Minute und hilft anderen Kundinnen.")
            ->action('Termin bewerten', $url)
            ->line('Der Link ist 30 Tage gültig und funktioniert nur für diesen Termin.');
    }
}
