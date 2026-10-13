<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    // Ersatztermine, die die Studioleitung anbietet
    Schema::create('appointment_alternatives', function (Blueprint $table) {
        $table->id();
        $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
        $table->date('date');
        $table->string('time', 5);
        $table->timestamps();
    });

    // Nachricht der Studioleitung an die Kundin
    Schema::table('appointments', function (Blueprint $table) {
        $table->text('admin_note')->nullable();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn('admin_note');
    });

    Schema::dropIfExists('appointment_alternatives');
}
};
