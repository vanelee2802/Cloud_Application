<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * role war ein enum (nur admin/employee/customer).
 * Jetzt normaler Text, damit auch 'owner' möglich ist.
 * Welche Rollen erlaubt sind, prüft der Code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'employee', 'customer'])
                ->default('customer')
                ->change();
        });
    }
};