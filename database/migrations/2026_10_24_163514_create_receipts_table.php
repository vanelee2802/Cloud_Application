<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rechnungen nach § 14 UStG.
 * Alle Angaben werden beim Ausstellen als Momentaufnahme gespeichert,
 * damit eine Rechnung sich später nie mehr ändert (auch wenn Preise
 * oder Studiodaten geändert werden).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            // Rechnungsnummer: fortlaufend pro Studio und Jahr, z. B. "R1-2026-00001"
            $table->string('number')->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('sequence');

            // Bezüge – payment_id eindeutig: pro Zahlung genau eine Rechnung
            $table->foreignId('payment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->constrained()->restrictOnDelete();
            $table->foreignId('nail_studio_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Rechnungsempfängerin (Momentaufnahme)
            $table->string('customer_name');
            $table->string('customer_email');

            // Rechnungsaussteller (Momentaufnahme)
            $table->string('studio_name');
            $table->string('studio_address');
            $table->string('tax_number', 50)->nullable();
            $table->string('vat_id', 20)->nullable();
            $table->boolean('is_small_business')->default(false);

            // Positionen als Liste: Bezeichnung, Menge, Einzelpreis (brutto, Cent)
            $table->json('items');

            // Beträge in Cent – keine Rundungsfehler
            $table->unsignedTinyInteger('vat_rate');
            $table->unsignedInteger('net_cents');
            $table->unsignedInteger('vat_cents');
            $table->unsignedInteger('gross_cents');

            // Leistungsdatum (Termin) und Rechnungsdatum
            $table->date('service_date');
            $table->timestamp('issued_at');

            $table->timestamps();

            // Keine doppelte Nummer im selben Nummernkreis
            $table->unique(['nail_studio_id', 'year', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};