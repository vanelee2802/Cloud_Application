<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        // Gäste haben kein Kundenkonto → user_id darf leer sein
        $table->unsignedBigInteger('user_id')->nullable()->change();

        // An wen gehen Bestätigung und Zahlungslink?
        $table->string('customer_name')->nullable();
        $table->string('customer_email')->nullable();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn(['customer_name', 'customer_email']);
        $table->unsignedBigInteger('user_id')->nullable(false)->change();
    });
}
};
