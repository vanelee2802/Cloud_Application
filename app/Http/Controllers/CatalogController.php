<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\DesignElement;
use App\Models\NailStudio;
use App\Models\PriceOption;
use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Eigener Katalog eines Studios – nur für die Inhaberin.
 * Muster, Farben, Effekte, Verzierungen und Materialien anlegen und entfernen.
 */
class CatalogController extends Controller
{
    /** Arten, für die ein Bild Pflicht ist (damit es direkt auf dem Nagel funktioniert) */
    private const IMAGE_REQUIRED = ['muster', 'effekt', 'verzierung'];

    /** Arten, die in price_options liegen */
    private const OPTION_TYPES = ['effekt', 'verzierung', 'material'];

    public function store(Request $request, NailStudio $studio): RedirectResponse
    {
        $this->authorizeOwner($request, $studio);

        $type = $request->input('type');

        $validated = $request->validate(
            [
                'type'     => ['required', Rule::in(['muster', 'farbe', 'effekt', 'verzierung', 'material'])],
                'name'     => ['required', 'string', 'max:100'],
                'price'    => [Rule::requiredIf($type !== 'farbe'), 'nullable', 'numeric', 'min:0', 'max:9999.99'],
                'category' => [Rule::requiredIf(in_array($type, ['muster', 'farbe'])), 'nullable', 'string', 'max:50'],
                'tags'     => ['nullable', 'string', 'max:255'],
                'hex_code' => [Rule::requiredIf($type === 'farbe'), 'nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'image'    => [
                    Rule::requiredIf(in_array($type, self::IMAGE_REQUIRED)),
                    'nullable',
                    'file',
                    'mimes:svg,png,webp',
                    'max:2048',
                ],
            ],
            [
                'name.required'     => 'Bitte gib einen Namen ein.',
                'price.required'    => 'Bitte gib einen Preis ein.',
                'price.numeric'     => 'Bitte gib eine gültige Zahl ein.',
                'category.required' => 'Bitte wähle eine Kategorie.',
                'hex_code.required' => 'Bitte wähle eine Farbe.',
                'hex_code.regex'    => 'Der Farbcode muss so aussehen: #A1B2C3.',
                'image.required'    => 'Bitte lade ein Bild hoch (SVG, PNG oder WebP).',
                'image.mimes'       => 'Erlaubt sind nur SVG, PNG oder WebP.',
                'image.max'         => 'Das Bild darf höchstens 2 MB groß sein.',
            ]
        );

        $imageUrl = $request->hasFile('image')
            ? Storage::url($request->file('image')->store("catalog/{$studio->id}", 'public'))
            : null;

        $price = isset($validated['price']) ? round((float) $validated['price'], 2) : null;

        $created = match ($type) {
                'muster' => DesignElement::forceCreate([
                'nail_studio_id' => $studio->id,
                'name'           => $validated['name'],
                'category'       => $validated['category'],
                'price_per_nail' => $price,
                'image_path'     => $imageUrl,
                'is_active'      => true,
            ]),

            'farbe' => Color::forceCreate([
                'nail_studio_id' => $studio->id,
                'name'           => $validated['name'],
                'hex_code'       => strtoupper($validated['hex_code']),
                'category'       => mb_strtolower($validated['category']),
            ]),

            default => PriceOption::forceCreate([
                'nail_studio_id' => $studio->id,
                'category'       => $type,
                'key'            => Str::slug($validated['name']) . '-' . Str::lower(Str::random(5)),
                'name'           => $validated['name'],
                'default_price'  => $price,
                'unit'           => $type === 'verzierung' ? 'nagel' : 'pauschal',
                'image_path'     => $imageUrl,
                'sort_order'     => 999,
                'is_active'      => true,
            ]),
        };

                // Tags für Muster: "herbst, gold" → vorhandene wiederverwenden, neue anlegen
        if ($type === 'muster' && filled($validated['tags'] ?? null)) {

            $tagIds = collect(explode(',', $validated['tags']))
                ->map(fn ($name) => mb_strtolower(trim($name)))
                ->filter()
                ->unique()
                ->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id);

            $created->tags()->sync($tagIds);
        }

        return back()->with('success', 'Eintrag wurde angelegt.');
    }

    /**
     * Eigenen Eintrag entfernen.
     * Wird er schon in gespeicherten Designs benutzt, wird er nur ausgeblendet.
     */
    public function destroy(Request $request, NailStudio $studio, string $type, int $id): RedirectResponse
    {
        $this->authorizeOwner($request, $studio);

        $model = match (true) {
            $type === 'muster'                     => DesignElement::class,
            $type === 'farbe'                      => Color::class,
            in_array($type, self::OPTION_TYPES)    => PriceOption::class,
            default                                => abort(404),
        };

        // Nur Einträge DIESES Studios – Platzhalter (NULL) und fremde Studios sind tabu
        $item = $model::where('nail_studio_id', $studio->id)->findOrFail($id);

        $image = $item->image_path ?? null;

        try {
            $item->delete();
        } catch (QueryException $e) {
            // In Benutzung (z. B. in gespeicherten Designs): nur ausblenden
            if (array_key_exists('is_active', $item->getAttributes())) {
                $item->forceFill(['is_active' => false])->save();

                return back()->with('success', 'Eintrag wird schon verwendet und wurde ausgeblendet.');
            }

            throw $e;
        }

        // Hochgeladenes Bild mit entfernen
        if ($image && str_starts_with($image, '/storage/')) {
            Storage::disk('public')->delete(Str::after($image, '/storage/'));
        }

        return back()->with('success', 'Eintrag wurde entfernt.');
    }

    private function authorizeOwner(Request $request, NailStudio $studio): void
    {
        abort_unless(
            $request->user()->isOwner()
                && $request->user()->ownedStudios()->whereKey($studio->id)->exists(),
            403
        );
    }
}
