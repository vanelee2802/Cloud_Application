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
        Schema::create('appointments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('nail_studio_id')->constrained();
    $table->foreignId('service_id')->constrained();
    $table->foreignId('employee_id')->nullable()->constrained('users');
    $table->foreignId('design_id')->nullable()->constrained();
    $table->date('date');
    $table->time('time');
    $table->enum('status', ['requested', 'confirmed', 'rejected', 'completed'])->default('requested');
    $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
