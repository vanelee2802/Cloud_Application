<?php

use Database\Seeders\DefaultPatternSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grunddaten für den Server (dort laufen keine Seeder):
 * Dienstleistungen, Nagelformen, Farben, Design-Elemente, Standard-Muster.
 *
 * Kann gefahrlos mehrfach laufen: Vorhandene Einträge
 * werden nicht doppelt angelegt.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Dienstleistungen für das erste Studio
        $studioId = DB::table('nail_studios')->orderBy('id')->value('id');

        if ($studioId) {
            $services = [
                ['name' => 'Maniküre + Design', 'price' => 25.00, 'duration_minutes' => 45],
                ['name' => 'Nur Maniküre',      'price' => 15.00, 'duration_minutes' => 30],
            ];

            foreach ($services as $service) {
                $this->insertIfMissing('services', [
                    'nail_studio_id' => $studioId,
                    'name'           => $service['name'],
                ], [
                    'price'            => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }

        // Nagelformen
        foreach (['Almond', 'Square', 'Coffin', 'Round', 'Stiletto'] as $shape) {
            $this->insertIfMissing('nail_shapes', ['name' => $shape], [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Farben (inkl. Blau, Grün, Gelb für die Filter-Buttons)
        $colors = [
            ['name' => 'Rot',     'hex_code' => '#E53935'],
            ['name' => 'Rosa',    'hex_code' => '#F48FB1'],
            ['name' => 'Nude',    'hex_code' => '#E0C097'],
            ['name' => 'Schwarz', 'hex_code' => '#212121'],
            ['name' => 'Weiß',    'hex_code' => '#FAFAFA'],
            ['name' => 'Blau',    'hex_code' => '#1E88E5'],
            ['name' => 'Grün',    'hex_code' => '#43A047'],
            ['name' => 'Gelb',    'hex_code' => '#FDD835'],
        ];

        foreach ($colors as $color) {
            $this->insertIfMissing('colors', ['name' => $color['name']], [
                'hex_code'   => $color['hex_code'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Einfache Design-Elemente (für alle Studios, aktiv)
        $elements = [
            ['category' => 'French',    'name' => 'Klassisch French', 'price_per_nail' => 2.00],
            ['category' => 'Chrome',    'name' => 'Chrome Effekt',    'price_per_nail' => 3.00],
            ['category' => 'Glitzer',   'name' => 'Silber-Glitzer',   'price_per_nail' => 1.00],
            ['category' => 'Ombre',     'name' => 'Ombre Verlauf',    'price_per_nail' => 2.50],
            ['category' => 'Steinchen', 'name' => 'Strasssteinchen',  'price_per_nail' => 1.50],
        ];

        foreach ($elements as $element) {
            $this->insertIfMissing('design_elements', [
                'name'           => $element['name'],
                'nail_studio_id' => null,
            ], [
                'category'       => $element['category'],
                'price_per_nail' => $element['price_per_nail'],
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // Standard-Muster aus database/data/default_patterns.json
        app(DefaultPatternSeeder::class)->run();
    }

    public function down(): void
    {
        // Bewusst leer: Grunddaten werden beim Zurückrollen nicht gelöscht
    }

    /** Legt einen Eintrag nur an, wenn es ihn (nach $match) noch nicht gibt */
    private function insertIfMissing(string $table, array $match, array $values): void
    {
        if (! DB::table($table)->where($match)->exists()) {
            DB::table($table)->insert($match + $values);
        }
    }
};