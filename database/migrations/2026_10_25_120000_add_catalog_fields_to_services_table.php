<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leistungen dynamisch machen:
 * - neue Spalten für Kategorie, Beschreibung, „ab“-Preis, Zusatzleistung, „Beliebt“, aktiv, Reihenfolge
 * - die Leistungen der bisherigen Preisseite für jedes vorhandene Studio anlegen
 *
 * Läuft beim Deployment automatisch. Mehrfaches Ausführen legt nichts doppelt an.
 */
return new class extends Migration
{
    private const DEFAULTS = [
        [
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'description'      => 'Neues Nagelset mit natürlichem Look.',
            'price'            => 45,
            'price_is_from'    => false,
            'duration_minutes' => 90,
            'is_addon'         => false,
            'is_popular'       => true,
        ],
        [
            'name'             => 'Neumodellage mit Farbe',
            'category'         => 'Neumodellage',
            'description'      => 'Neues Set mit einer Farbe deiner Wahl.',
            'price'            => 50,
            'price_is_from'    => false,
            'duration_minutes' => 100,
            'is_addon'         => false,
            'is_popular'       => false,
        ],
        [
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'description'      => 'Auffüllen und Formen der bestehenden Modellage.',
            'price'            => 35,
            'price_is_from'    => false,
            'duration_minutes' => 60,
            'is_addon'         => false,
            'is_popular'       => false,
        ],
        [
            'name'             => 'Auffüllen mit Farbe',
            'category'         => 'Auffüllen',
            'description'      => 'Auffüllen inklusive Farblack.',
            'price'            => 40,
            'price_is_from'    => false,
            'duration_minutes' => 75,
            'is_addon'         => false,
            'is_popular'       => true,
        ],
        [
            'name'             => 'French Design',
            'category'         => 'Extras',
            'description'      => 'Klassische French-Spitzen für einen eleganten Look.',
            'price'            => 8,
            'price_is_from'    => false,
            'duration_minutes' => 15,
            'is_addon'         => true,
            'is_popular'       => false,
        ],
        [
            'name'             => 'Nail Art',
            'category'         => 'Extras',
            'description'      => 'Individuelle Verzierungen nach Wunsch.',
            'price'            => 5,
            'price_is_from'    => true,
            'duration_minutes' => 0,
            'is_addon'         => true,
            'is_popular'       => false,
        ],
    ];

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('name');
            $table->text('description')->nullable();
            $table->boolean('price_is_from')->default(false);
            $table->boolean('is_addon')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });

        // Bestehende Leistungen („Maniküre + Design“, „Nur Maniküre“) behalten und hinten einsortieren
        DB::table('services')
            ->whereNull('category')
            ->update(['category' => 'Maniküre', 'sort_order' => 100]);

        $now = now();

        foreach (DB::table('nail_studios')->pluck('id') as $studioId) {
            foreach (self::DEFAULTS as $index => $service) {
                $exists = DB::table('services')
                    ->where('nail_studio_id', $studioId)
                    ->where('name', $service['name'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('services')->insert([
                    ...$service,
                    'nail_studio_id' => $studioId,
                    'is_active'      => true,
                    'sort_order'     => ($index + 1) * 10,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }

    /**
     * Entfernt nur die neuen Spalten. Die angelegten Leistungen bleiben stehen,
     * weil inzwischen Termine darauf verweisen können.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'description',
                'price_is_from',
                'is_addon',
                'is_popular',
                'is_active',
                'sort_order',
            ]);
        });
    }
};
