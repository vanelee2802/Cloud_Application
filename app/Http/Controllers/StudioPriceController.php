<?php

namespace App\Http\Controllers;

use App\Models\DesignElement;
use App\Models\NailStudio;
use App\Models\PriceOption;
use App\Models\Service;
use App\Services\PriceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Preise pro Studio – nur für die Inhaberin.
 */
class StudioPriceController extends Controller
{
    public function __construct(private PriceService $prices)
    {
    }

    /** Preis-Seite: Studio wählen, Preise je Kategorie anzeigen */
    public function index(Request $request): Response
    {
        $owner   = $request->user();
        $studios = $owner->ownedStudios()->orderBy('name')->get(['id', 'name']);

        // Gewähltes Studio (?studio=ID), sonst das erste
        $studio = $studios->firstWhere('id', (int) $request->query('studio'))
            ?? $studios->first();

        return Inertia::render('Preise/Index', [
            'studios'          => $studios,
            'selectedStudioId' => $studio?->id,
            'prices'           => $studio
                ? $this->prices->priceList(NailStudio::find($studio->id))
                : null,
        ]);
    }

    /**
     * Einen Preis speichern.
     * type: service | design | option
     * price: Zahl, oder leer → Standardpreis gilt wieder (nicht bei Dienstleistungen)
     */
    public function update(Request $request, NailStudio $studio): RedirectResponse
    {
        // Nur eigene Studios
        abort_unless(
            in_array($studio->id, $request->user()->manageableStudioIds()),
            403
        );

        $validated = $request->validate(
            [
                'type'  => ['required', Rule::in(['service', 'design', 'option'])],
                'id'    => ['required', 'integer'],
                'price' => [
                    Rule::requiredIf($request->input('type') === 'service'),
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999.99',
                ],
            ],
            [
                'price.required' => 'Bitte gib einen Preis ein.',
                'price.numeric'  => 'Bitte gib eine gültige Zahl ein.',
                'price.min'      => 'Der Preis darf nicht negativ sein.',
                'price.max'      => 'Der Preis ist zu hoch.',
            ]
        );

        $price = $validated['price'] === null ? null : (float) $validated['price'];

        switch ($validated['type']) {

            // Dienstleistungen gehören direkt zum Studio
            case 'service':
                $service = Service::where('nail_studio_id', $studio->id)
                    ->findOrFail($validated['id']);

                $service->update(['price' => round($price, 2)]);
                break;

            // Muster: eigener Preis oder zurück zum Standard
            case 'design':
                $this->prices->setPrice(
                    $studio,
                    DesignElement::findOrFail($validated['id']),
                    $price
                );
                break;

            // Effekte, Verzierungen, Längen, Materialien
            case 'option':
                $this->prices->setPrice(
                    $studio,
                    PriceOption::findOrFail($validated['id']),
                    $price
                );
                break;
        }

        return back()->with('success', 'Preis gespeichert.');
    }
}