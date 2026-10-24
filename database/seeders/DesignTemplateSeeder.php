<?php

namespace Database\Seeders;

use App\Models\DesignTemplate;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Übernimmt die Vorlagen aus database/data/design_templates.json.
 *
 * - Vorlagen mit gleichem Namen werden aktualisiert, nicht doppelt angelegt
 * - Tags werden wie bei den Mustern klein geschrieben und wiederverwendet
 * - Kann beliebig oft laufen
 */
class DesignTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = json_decode(
            File::get(database_path('data/design_templates.json')),
            true
        );

        foreach ($templates as $index => $data) {

            $template = DesignTemplate::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'] ?? null,
                    'nails'       => $data['nails'],
                    'sort_order'  => $index,
                    'is_active'   => true,
                ]
            );

            // Tags: vorhandene wiederverwenden, neue anlegen
            $tagIds = collect($data['tags'] ?? [])
                ->map(fn ($name) => Tag::firstOrCreate([
                    'name' => mb_strtolower(trim($name)),
                ])->id);

            $template->tags()->sync($tagIds);
        }
    }
}