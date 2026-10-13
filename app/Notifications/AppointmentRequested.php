<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRequested extends Notification
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
        $a = $this->appointment;
        $time = substr($a->time, 0, 5);

        return (new MailMessage)
            ->subject("Neue Terminanfrage für heute um {$time} Uhr")
            ->greeting('Hallo,')
            ->line("{$a->customer_name} ({$a->customer_email}) möchte heute um {$time} Uhr einen Termin.")
            ->line('Service: ' . $a->service->name)
            ->line('Bitte bestätigen, absagen oder Alternativen anbieten.')
            ->action('Anfrage bearbeiten', url('/StudioDashboard'))
            ->salutation('Dein Buchungssystem');
    }
}