<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AlternativesOffered extends Notification
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

        // Link zur Terminseite – dort wählt die Kundin einen Vorschlag
        $url = URL::temporarySignedRoute('appointments.manage', now()->addDays(2), [
            'appointment' => $a->id,
        ]);

        $date = Carbon::parse($a->date)->format('d.m.Y');
        $time = substr($a->time, 0, 5);

        $mail = (new MailMessage)
            ->subject('Dein Wunschtermin ist leider belegt – wir haben Alternativen für dich')
            ->greeting('Hallo ' . $a->customer_name . ',')
            ->line("dein Wunschtermin am {$date} um {$time} Uhr ist leider nicht möglich. Wir können dir folgende Termine anbieten:");

        // Eine Zeile pro Vorschlag
        foreach ($a->alternatives as $alt) {
            $altDate = Carbon::parse($alt->date)->format('d.m.Y');
            $mail->line("• {$altDate} um {$alt->time} Uhr");
        }

        if ($a->admin_note) {
            $mail->line('Nachricht vom Studio: ' . $a->admin_note);
        }

        return $mail
            ->line('Wer zuerst bucht, bekommt den Termin – am besten wählst du gleich.')
            ->action('Termin auswählen', $url)
            ->salutation('Viele Grüße, dein MixMatch Nagelstudio');
    }
}