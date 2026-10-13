<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL: Constraint direkt löschen
            DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
        } else {
            // SQLite: Spalte neu definieren, dabei fällt die Einschränkung weg
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->change();
            });
        }
    }

    public function down(): void
    {
        // bleibt leer, die Einschränkung wird nicht wiederhergestellt
    }
};