<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wöchentliche Schichten der Mitarbeiterinnen.
 * Ein Eintrag pro Person und Wochentag. Kein Eintrag = frei.
 *
 * weekday: 1 = Montag … 7 = Sonntag (ISO-8601, wie Carbon->dayOfWeekIso)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['user_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};
