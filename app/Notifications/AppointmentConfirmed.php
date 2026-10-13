<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AppointmentConfirmed extends Notification
{
    public function __construct(public Appointment $appointment)
    {
    }

    // Über welchen Weg wird benachrichtigt? Hier nur per E-Mail.
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    // Inhalt der E-Mail
    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment;

        // Link zur Terminseite – funktioniert auch ohne Login, 2 Tage gültig
        $url = URL::temporarySignedRoute('appointments.manage', now()->addDays(2), [
            'appointment' => $a->id,
        ]);

        $date = Carbon::parse($a->date)->format('d.m.Y');
        $time = substr($a->time, 0, 5);
        $deadline = $a->payment_expires_at->format('H:i');

        return (new MailMessage)
            ->subject('Dein Termin ist bestätigt – bitte jetzt bezahlen')
            ->greeting('Hallo ' . $a->customer_name . ',')
            ->line("gute Nachrichten: Dein Termin am {$date} um {$time} Uhr ist bestätigt.")
            ->line("Bitte bezahle bis {$deadline} Uhr, sonst wird der Termin wieder freigegeben.")
            ->action('Jetzt bezahlen', $url)
            ->salutation('Viele Grüße, dein MixMatch Nagelstudio');
    }
}