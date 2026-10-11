<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    // Deine bisherigen Status – bleiben unverändert gültig
    case Requested = 'requested';     // Anfrage für heute, wartet auf Mitarbeiter
    case Confirmed = 'confirmed';     // bezahlt und fest gebucht
    case Rejected = 'rejected';       // abgesagt
    case Completed = 'completed';     // Termin hat stattgefunden

    // Neu für den Buchungsablauf
    case Held = 'held';                                // 3 Minuten reserviert
    case AwaitingPayment = 'awaiting_payment';         // angenommen, Zahlung offen
    case AlternativesOffered = 'alternatives_offered'; // Ersatztermine angeboten
    case Expired = 'expired';                          // Frist abgelaufen
    case Cancelled = 'cancelled';                      // vom Kunden verworfen

    // Deutscher Text für die Anzeige im Frontend und in E-Mails
    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Anfrage wird geprüft',
            self::Confirmed => 'Gebucht',
            self::Rejected => 'Abgesagt',
            self::Completed => 'Abgeschlossen',
            self::Held => 'Reserviert',
            self::AwaitingPayment => 'Bestätigt – Zahlung offen',
            self::AlternativesOffered => 'Alternativtermine verfügbar',
            self::Expired => 'Abgelaufen',
            self::Cancelled => 'Storniert',
        };
    }
}