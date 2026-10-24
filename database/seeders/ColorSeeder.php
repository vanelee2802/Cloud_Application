<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Übernimmt die Farben aus public/colors.json in die Datenbank.
 *
 * - Neue Farben werden angelegt
 * - Vorhandene Farben (gleicher Name) bekommen Farbcode und Kategorie aus der Datei
 * - Kann beliebig oft laufen, nichts wird doppelt angelegt
 */
class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = json_decode(File::get(public_path('colors.json')), true);

        foreach ($colors as $color) {
            Color::updateOrCreate(
                ['name' => $color['name']],
                [
                    'hex_code' => $color['hex_code'],
                    'category' => $color['category'] ?? 'sonstige',
                ]
            );
        }
    }
}