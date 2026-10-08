<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spalte nur anlegen, wenn es sie noch nicht gibt
        if (Schema::hasColumn('users', 'nail_studio_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('nail_studio_id')
                ->default(1)
                ->after('role');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'nail_studio_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('nail_studio_id');
            });
        }
    }
};