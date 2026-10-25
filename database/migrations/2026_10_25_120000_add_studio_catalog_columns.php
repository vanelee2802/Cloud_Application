<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eigener Katalog pro Studio.
 *
 * nail_studio_id = NULL  → Platzhalter für alle Studios
 * nail_studio_id = ID    → eigener Eintrag dieses Studios (von der Inhaberin angelegt)
 *
 * design_elements hat beide Spalten schon (nail_studio_id, image_path).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->foreignId('nail_studio_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('price_options', function (Blueprint $table) {
            $table->foreignId('nail_studio_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();

            // Gleicher Schlüssel darf jetzt je Studio einmal vorkommen
            $table->dropUnique(['category', 'key']);
            $table->unique(['nail_studio_id', 'category', 'key'], 'price_options_studio_unique');
        });
    }

    public function down(): void
    {
        Schema::table('price_options', function (Blueprint $table) {
            $table->dropUnique('price_options_studio_unique');
            $table->unique(['category', 'key']);
            $table->dropConstrainedForeignId('nail_studio_id');
            $table->dropColumn('image_path');
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nail_studio_id');
        });
    }
};
