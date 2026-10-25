<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bewertungen nach dem Termin:
 * - appointments.completed_at: wann der Termin abgeschlossen wurde
 * - appointments.review_requested_at: wann die Danke-Mail rausging (verhindert doppelte Mails)
 * - reviews: eine Bewertung pro Termin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('review_requested_at')->nullable();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('nail_studio_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('author_name', 60);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['nail_studio_id', 'is_hidden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['completed_at', 'review_requested_at']);
        });
    }
};
