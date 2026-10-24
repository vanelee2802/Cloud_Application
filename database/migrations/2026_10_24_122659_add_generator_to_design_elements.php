<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generierte Muster: statt eines Bildes werden nur die Art des Musters
 * und die Startwerte der Regler gespeichert. Der Browser berechnet
 * daraus die Farbschichten (siehe patternGenerators.js).
 *
 * generator: z. B. "french", "streifen", "punkte", "karo"
 * params:    Startwerte, z. B. { "tiefe": 26, "bogen": 13 }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {

            $table->string('generator')
                ->nullable()
                ->after('layers');

            $table->json('params')
                ->nullable()
                ->after('generator');
        });
    }

    public function down(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {
            $table->dropColumn(['generator', 'params']);
        });
    }
};