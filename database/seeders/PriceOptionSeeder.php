<?php

namespace Database\Seeders;

use App\Models\PriceOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Übernimmt Effekte, Verzierungen, Längen und Materialien
 * aus database/data/price_options.json.
 *
 * - Neue Optionen werden mit ihrem Standardpreis angelegt
 * - Vorhandene behalten ihren Standardpreis (nur Name, Einheit, Reihenfolge werden aktualisiert)
 * - Kann beliebig oft laufen
 */
class PriceOptionSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            File::get(database_path('data/price_options.json')),
            true
        );

        foreach ($data as $category => $gruppe) {

            foreach ($gruppe['eintraege'] as $index => $eintrag) {

                $option = PriceOption::firstOrNew([
                    'category' => $category,
                    'key'      => $eintrag['key'],
                ]);

                // Preis nur beim ersten Anlegen setzen
                if (! $option->exists) {
                    $option->default_price = $eintrag['preis'];
                }

                $option->fill([
                    'name'       => $eintrag['name'],
                    'unit'       => $gruppe['einheit'],
                    'sort_order' => $index,
                    'is_active'  => true,
                ])->save();
            }
        }
    }
}