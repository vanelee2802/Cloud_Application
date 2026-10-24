<?php

namespace App\Notifications;

use App\Models\Receipt;
use App\Services\ReceiptPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Kopie jeder Rechnung an die Inhaberin – für Buchhaltung und Finanzamt.
 * Läuft im Hintergrund über die Queue.
 */
class ReceiptIssuedForStudio extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Receipt $receipt)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $r   = $this->receipt;
        $pdf = app(ReceiptPdf::class);

        return (new MailMessage)
            ->subject("Rechnungskopie {$r->number} – {$r->studio_name}")
            ->greeting('Hallo,')
            ->line("es wurde eine neue Rechnung für {$r->studio_name} ausgestellt.")
            ->line("Rechnungsnummer: {$r->number}")
            ->line('Kundin: ' . $r->customer_name)
            ->line('Leistungsdatum: ' . $r->service_date->format('d.m.Y'))
            ->line('Gesamtbetrag: ' . \App\Models\Receipt::euro($r->gross_cents))
            ->line('Die Rechnung liegt als PDF im Anhang – zur Ablage für Buchhaltung und Steuer.')
            ->attachData($pdf->render($r), $pdf->filename($r), [
                'mime' => 'application/pdf',
            ])
            ->salutation('Viele Grüße, MixMatch');
    }
}