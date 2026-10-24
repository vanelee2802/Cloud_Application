<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Steuerdaten für Rechnungen nach § 14 UStG.
 * - tax_number:        Steuernummer vom Finanzamt
 * - vat_id:            USt-IdNr. (optional, z. B. DE123456789)
 * - is_small_business: Kleinunternehmer nach § 19 UStG → keine MwSt. ausweisen
 * - vat_rate:          MwSt.-Satz in Prozent (Standard 19)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nail_studios', function (Blueprint $table) {
            $table->string('tax_number', 50)->nullable();
            $table->string('vat_id', 20)->nullable();
            $table->boolean('is_small_business')->default(false);
            $table->unsignedTinyInteger('vat_rate')->default(19);
        });
    }

    public function down(): void
    {
        Schema::table('nail_studios', function (Blueprint $table) {
            $table->dropColumn(['tax_number', 'vat_id', 'is_small_business', 'vat_rate']);
        });
    }
};