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
        Schema::create('design_nails', function (Blueprint $table) {
    $table->id();
    $table->foreignId('design_id')->constrained()->cascadeOnDelete();
    $table->integer('nail_position');
    $table->foreignId('nail_shape_id')->constrained();
    $table->foreignId('color_id')->constrained();
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_nails');
    }
};
