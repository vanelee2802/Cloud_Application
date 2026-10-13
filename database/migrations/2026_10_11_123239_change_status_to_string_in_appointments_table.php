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
    Schema::table('appointments', function (Blueprint $table) {
        $table->string('status', 30)->default('requested')->change();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->enum('status', ['requested', 'confirmed', 'rejected', 'completed'])
            ->default('requested')
            ->change();
    });
}
};
