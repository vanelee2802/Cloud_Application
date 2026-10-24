<?php

namespace App\Services;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\URL;

/**
 * Erzeugt die Rechnung als PDF (inkl. QR-Code zur Terminseite).
 */
class ReceiptPdf
{
    /** PDF als Inhalt (Bytes) – für Mail-Anhang und Download */
    public function render(Receipt $receipt): string
    {
        // Signierter Link zur Terminseite – funktioniert auch ohne Login
        $appointmentUrl = URL::signedRoute('appointments.manage', [
            'appointment' => $receipt->appointment_id,
        ]);

        // QR-Code als SVG (Data-URI), braucht keine Zusatz-Erweiterung
        $qrCode = (new QRCode())->render($appointmentUrl);

        return Pdf::loadView('pdf.receipt', [
            'r'              => $receipt,
            'qrCode'         => $qrCode,
            'appointmentUrl' => $appointmentUrl,
        ])
            ->setPaper('a4')
            ->output();
    }

    /** Dateiname, z. B. "Rechnung-R1-2026-00001.pdf" */
    public function filename(Receipt $receipt): string
    {
        return "Rechnung-{$receipt->number}.pdf";
    }
}