<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Services\ReceiptPdf;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AppointmentPaid extends Notification
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

        // Link zur Terminseite, 30 Tage gültig
        $url = URL::temporarySignedRoute('appointments.manage', now()->addDays(30), [
            'appointment' => $a->id,
        ]);

        $date  = Carbon::parse($a->date)->format('d.m.Y');
        $time  = substr($a->time, 0, 5);
        $price = number_format($a->payment->amount, 2, ',', '.') . ' €';

        $mail = (new MailMessage)
            ->subject("Buchungsbestätigung: dein Termin am {$date} um {$time} Uhr")
            ->greeting('Hallo ' . $a->customer_name . ',')
            ->line("vielen Dank für deine Zahlung von {$price}.")
            ->line('Dein Termin ist fest gebucht:')
            ->line("{$a->service->name} am {$date} um {$time} Uhr")
            ->action('Termin ansehen', $url);

        // Rechnung als PDF anhängen (wird beim Bezahlen automatisch erstellt)
        $receipt = $a->payment?->receipt;

        if ($receipt) {
            $pdf = app(ReceiptPdf::class);

            $mail->line("Deine Rechnung {$receipt->number} findest du im Anhang.")
                ->attachData($pdf->render($receipt), $pdf->filename($receipt), [
                    'mime' => 'application/pdf',
                ]);
        }

        return $mail
            ->line('Wir freuen uns auf dich!')
            ->salutation('Viele Grüße, dein MixMatch Nagelstudio');
    }
}