<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Umfärbbare Muster: Ein Muster kann aus mehreren Farbschichten bestehen.
 *
 * Beispiel für "layers" (JSON):
 * [
 *     { "name": "Spitze", "mask": "/images/.../french-spitze.svg", "color": "#FFFFFF" }
 * ]
 *
 * - name:  Bezeichnung für die Nutzerin (z. B. "Spitze", "Linien")
 * - mask:  Bild, das nur die FORM enthält (Rest transparent)
 * - color: Standardfarbe, die die Nutzerin ändern kann
 *
 * NULL = normales, deckendes Muster (wie bisher über image_path)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {
            $table->json('layers')
                ->nullable()
                ->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {
            $table->dropColumn('layers');
        });
    }
};