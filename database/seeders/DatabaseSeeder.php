<?php

namespace Database\Seeders;

use App\Models\NailStudio;
use App\Models\Service;
use App\Models\NailShape;
use App\Models\Color;
use App\Models\DesignElement;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Euer eines Nagelstudio
        $studio = NailStudio::create([
            'name' => 'Nagelstudio Mix and Match',
            'address' => 'Musterstraße 1, 30159 Hannover',
            'opening_hours' => 'Mo–Fr 9–18 Uhr, Sa 10–14 Uhr',
        ]);

        // Dienstleistungen
        Service::create([
            'nail_studio_id' => $studio->id,
            'name' => 'Maniküre + Design',
            'price' => 25.00,
            'duration_minutes' => 45,
        ]);

        Service::create([
            'nail_studio_id' => $studio->id,
            'name' => 'Nur Maniküre',
            'price' => 15.00,
            'duration_minutes' => 30,
        ]);

        // Nagelformen
        $shapes = ['Almond', 'Square', 'Coffin', 'Round', 'Stiletto'];
        foreach ($shapes as $shape) {
            NailShape::create(['name' => $shape]);
        }

        // Farben
        $colors = [
            ['name' => 'Rot', 'hex_code' => '#E53935'],
            ['name' => 'Rosa', 'hex_code' => '#F48FB1'],
            ['name' => 'Nude', 'hex_code' => '#E0C097'],
            ['name' => 'Schwarz', 'hex_code' => '#212121'],
            ['name' => 'Weiß', 'hex_code' => '#FAFAFA'],
        ];
        foreach ($colors as $color) {
            Color::create($color);
        }

        // Design-Elemente mit Preisen
        $elements = [
            ['category' => 'French', 'name' => 'Klassisch French', 'price_per_nail' => 2.00],
            ['category' => 'Chrome', 'name' => 'Chrome Effekt', 'price_per_nail' => 3.00],
            ['category' => 'Glitzer', 'name' => 'Silber-Glitzer', 'price_per_nail' => 1.00],
            ['category' => 'Ombre', 'name' => 'Ombre Verlauf', 'price_per_nail' => 2.50],
            ['category' => 'Steinchen', 'name' => 'Strasssteinchen', 'price_per_nail' => 1.50],
        ];
        foreach ($elements as $element) {
            DesignElement::create($element);
        }
    }
}