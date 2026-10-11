<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AppointmentDeclined extends Notification
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

        $date = Carbon::parse($a->date)->format('d.m.Y');
        $time = substr($a->time, 0, 5);

        $mail = (new MailMessage)
            ->subject('Dein Terminwunsch konnte leider nicht angenommen werden')
            ->greeting('Hallo ' . $a->customer_name . ',')
            ->line("leider können wir deinen Termin am {$date} um {$time} Uhr nicht anbieten.");

        // Nachricht der Studioleitung nur anzeigen, wenn es eine gibt
        if ($a->admin_note) {
            $mail->line('Nachricht vom Studio: ' . $a->admin_note);
        }

        return $mail
            ->action('Neuen Termin buchen', url('/'))
            ->salutation('Viele Grüße, dein MixMatch Nagelstudio');
    }
}