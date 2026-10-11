<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->string('hold_token', 64)->nullable();
        $table->timestamp('hold_expires_at')->nullable();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn(['hold_token', 'hold_expires_at']);
    });
}
};
