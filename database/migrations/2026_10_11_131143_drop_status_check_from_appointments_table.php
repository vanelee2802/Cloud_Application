<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   public function up(): void
{
    // Alte Prüfregel aus dem ursprünglichen enum() entfernen.
    // Erlaubte Werte regelt ab jetzt unser PHP-Code.
    DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
}

public function down(): void
{
    DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('requested', 'confirmed', 'rejected', 'completed'))");
}
};
