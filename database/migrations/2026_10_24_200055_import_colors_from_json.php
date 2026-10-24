<?php

use Database\Seeders\ColorSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Übernimmt die Farben aus public/colors.json in die Datenbank.
 * Nötig, weil auf dem Server keine Seeder laufen.
 * Ruft nur den ColorSeeder auf – sonst nichts.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(ColorSeeder::class)->run();
    }

    public function down(): void
    {
        // Bewusst leer: Farben werden beim Zurückrollen nicht gelöscht
    }
};