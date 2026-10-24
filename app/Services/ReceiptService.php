<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\ReceiptIssuedForStudio;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Stellt für eine bezahlte Zahlung genau eine Rechnung aus.
 *
 * - Nur bei status = 'paid'
 * - Pro Zahlung höchstens eine Rechnung (doppelte Webhooks sind egal)
 * - Fortlaufende Nummer pro Studio und Jahr, ohne Lücken
 * - Alle Angaben als Momentaufnahme, damit die Rechnung sich nie ändert
 * - Kopie per Mail an die Inhaberin (für Buchhaltung und Finanzamt)
 */
class ReceiptService
{
    public function issueFor(Payment $payment): ?Receipt
    {
        if ($payment->status !== 'paid') {
            return null;
        }

        // Schon eine Rechnung vorhanden → diese zurückgeben
        if ($existing = Receipt::where('payment_id', $payment->id)->first()) {
            return $existing;
        }

        $payment->loadMissing('appointment.nailStudio', 'appointment.service', 'appointment.design');

        $appointment = $payment->appointment;
        $studio      = $appointment?->nailStudio;

        if (! $appointment || ! $studio) {
            Log::warning('Rechnung nicht möglich: Termin oder Studio fehlt', ['payment_id' => $payment->id]);
            return null;
        }

        if (! $studio->hasTaxData()) {
            Log::warning('Rechnung ohne Steuernummer ausgestellt – bitte im Studio nachtragen', [
                'nail_studio_id' => $studio->id,
            ]);
        }

        // Beträge: Preise sind brutto gespeichert → MwSt. herausrechnen
        $grossCents = (int) round(((float) $payment->amount) * 100);
        $vatRate    = $studio->is_small_business ? 0 : (int) $studio->vat_rate;
        $netCents   = $vatRate > 0
            ? (int) round($grossCents * 100 / (100 + $vatRate))
            : $grossCents;
        $vatCents   = $grossCents - $netCents;

        $data = [
            'payment_id'        => $payment->id,
            'appointment_id'    => $appointment->id,
            'nail_studio_id'    => $studio->id,
            'user_id'           => $appointment->user_id
                ?? User::where('email', $appointment->customer_email)->value('id'),

            'customer_name'     => $appointment->customer_name ?? $appointment->user?->name ?? 'Kundin',
            'customer_email'    => $appointment->customer_email ?? $appointment->user?->email ?? '',

            'studio_name'       => $studio->name,
            'studio_address'    => $studio->address,
            'tax_number'        => $studio->tax_number,
            'vat_id'            => $studio->vat_id,
            'is_small_business' => (bool) $studio->is_small_business,

            'items'             => $this->buildItems($appointment, $grossCents),

            'vat_rate'          => $vatRate,
            'net_cents'         => $netCents,
            'vat_cents'         => $vatCents,
            'gross_cents'       => $grossCents,

            'service_date'      => $appointment->date,
            'issued_at'         => now(),
        ];

        $year = now()->year;

        // Nummer vergeben – bei gleichzeitigen Zahlungen bis zu 3 Versuche
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $receipt = DB::transaction(function () use ($data, $studio, $year) {
                    $sequence = (int) Receipt::where('nail_studio_id', $studio->id)
                        ->where('year', $year)
                        ->max('sequence') + 1;

                    return Receipt::create($data + [
                        'year'     => $year,
                        'sequence' => $sequence,
                        'number'   => sprintf('R%d-%d-%05d', $studio->id, $year, $sequence),
                    ]);
                });

                // Erst nach erfolgreichem Speichern: Kopie ans Studio
                $this->sendStudioCopy($receipt);

                return $receipt;
            } catch (UniqueConstraintViolationException $e) {
                // Rechnung wurde gerade parallel erstellt → diese nehmen
                if ($existing = Receipt::where('payment_id', $payment->id)->first()) {
                    return $existing;
                }

                if ($attempt === 3) {
                    throw $e;
                }
            }
        }

        return null;
    }

    /**
     * Positionen der Rechnung. Ergeben sie nicht genau den bezahlten Betrag,
     * wird eine einzige Sammelposition verwendet – die Summe muss stimmen.
     */
    private function buildItems(Appointment $appointment, int $grossCents): array
    {
        $items = [];

        if ($appointment->service) {
            $items[] = [
                'label'       => $appointment->service->name,
                'quantity'    => 1,
                'price_cents' => (int) round(((float) $appointment->service->price) * 100),
            ];
        }

        $designPrice = (float) data_get($appointment, 'design.price', 0);

        if ($appointment->design && $designPrice > 0) {
            $items[] = [
                'label'       => 'Individuelles Nageldesign',
                'quantity'    => 1,
                'price_cents' => (int) round($designPrice * 100),
            ];
        }

        $sum = array_sum(array_map(fn ($i) => $i['price_cents'] * $i['quantity'], $items));

        if ($sum !== $grossCents) {
            return [[
                'label'       => $appointment->service?->name ?? 'Nageldesign-Termin',
                'quantity'    => 1,
                'price_cents' => $grossCents,
            ]];
        }

        return $items;
    }

    /** Kopie an die Inhaberin des Studios (für die Buchhaltung) */
    private function sendStudioCopy(Receipt $receipt): void
    {
        $ownerEmail = $receipt->nailStudio?->owner?->email;

        if (! $ownerEmail) {
            Log::warning('Keine Inhaberin für Rechnungskopie gefunden', ['receipt_id' => $receipt->id]);
            return;
        }

        try {
            Notification::route('mail', $ownerEmail)
                ->notify(new ReceiptIssuedForStudio($receipt));
        } catch (\Throwable $e) {
            // Mailfehler dürfen die Rechnung nicht verhindern
            report($e);
        }
    }
}