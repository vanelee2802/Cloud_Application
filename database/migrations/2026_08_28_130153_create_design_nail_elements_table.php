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
        Schema::create('design_nail_elements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('design_nail_id')->constrained()->cascadeOnDelete();
    $table->foreignId('design_element_id')->constrained();
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_nail_elements');
    }
};
