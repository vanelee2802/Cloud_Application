<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->string('slot_key')->nullable()->unique();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropUnique(['slot_key']);
        $table->dropColumn('slot_key');
    });
}
};
