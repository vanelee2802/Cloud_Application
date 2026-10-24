<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Einmalige Einrichtung: Inhaberin festlegen und Studios zuordnen.
 * Läuft automatisch über die Pipeline. Ist sicher, wenn sie
 * mehrfach läuft oder schon alles eingerichtet ist (z. B. lokal).
 */
return new class extends Migration
{
    private string $ownerEmail = 'oetken45@gmail.com';

    public function up(): void
    {
        $owner = DB::table('users')->where('email', $this->ownerEmail)->first();

        // Konto existiert (noch) nicht: nichts tun
        if (! $owner) {
            return;
        }

        // 1. Zur Inhaberin machen
        DB::table('users')->where('id', $owner->id)->update([
            'role'           => 'owner',
            'nail_studio_id' => null,
            'updated_at'     => now(),
        ]);

        // 2. Noch kein Studio vorhanden: eins anlegen
        if (DB::table('nail_studios')->count() === 0) {
            DB::table('nail_studios')->insert([
                'name'       => 'Nagelstudio Mix and Match',
                'address'    => 'Musterstraße 1, 38440 Wolfsburg',
                'owner_id'   => $owner->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        // 3. Vorhandene Studios ohne Inhaberin ihr zuordnen
        DB::table('nail_studios')
            ->whereNull('owner_id')
            ->update(['owner_id' => $owner->id]);
    }

    public function down(): void
    {
        // Bewusst leer: Daten werden beim Zurückrollen nicht verändert
    }
};