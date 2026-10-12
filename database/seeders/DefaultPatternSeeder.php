<?php

namespace Database\Seeders;

use App\Models\DesignElement;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DefaultPatternSeeder extends Seeder
{
    /**
     * Legt die Standard-Muster an (nail_studio_id = NULL → für alle Studios).
     * Quelle: database/data/default_patterns.json
     *
     * Kann beliebig oft ausgeführt werden: Vorhandene Muster werden
     * aktualisiert statt doppelt angelegt.
     */
    public function run(): void
    {
        $patterns = json_decode(
            File::get(database_path('data/default_patterns.json')),
            true
        );

        foreach ($patterns as $data) {

            // Muster anlegen oder aktualisieren
            $element = DesignElement::updateOrCreate(
                [
                    'name'           => $data['name'],
                    'nail_studio_id' => null,
                ],
                [
                    'category'       => $data['category'],
                    'price_per_nail' => $data['price_per_nail'],
                    'image_path'     => $data['image'],
                    'is_active'      => true,
                ]
            );

            // Tags anlegen (falls neu) und mit dem Muster verknüpfen
            $tagIds = collect($data['tags'])
                ->map(fn ($name) => Tag::firstOrCreate([
                    'name' => mb_strtolower(trim($name)),
                ])->id);

            $element->tags()->sync($tagIds);
        }
    }
}