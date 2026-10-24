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

            // Nur anlegen, wenn es das Muster noch nicht gibt.
            // Vorhandene Muster (z. B. vom Studio geänderte Preise)
            // bleiben unverändert → sicher bei jedem Deployment.
            $element = DesignElement::firstOrCreate(
                [
                    'name'           => $data['name'],
                    'nail_studio_id' => null,
                ],
                [
                    'category'       => $data['category'],
                    'price_per_nail' => $data['price_per_nail'],
                    'image_path'     => $data['image'] ?? null,
                    'layers'         => $data['layers'] ?? null,
                    'generator'      => $data['generator'] ?? null,
                    'params'         => $data['params'] ?? null,
                    'is_active'      => true,
                ]
            );

            // Tags nur bei neu angelegten Mustern setzen
            if ($element->wasRecentlyCreated) {

                $tagIds = collect($data['tags'] ?? [])
                    ->map(fn ($name) => Tag::firstOrCreate([
                        'name' => mb_strtolower(trim($name)),
                    ])->id);

                $element->tags()->sync($tagIds);
            }
        }
    }
}