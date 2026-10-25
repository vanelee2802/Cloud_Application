<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Color;
use App\Models\Design;
use App\Models\DesignElement;
use App\Models\NailStudio;
use App\Models\PriceOption;
use App\Models\Service;
use App\Models\StudioPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Preise und Katalog pro Studio.
 *
 * Sichtbar für ein Studio sind:
 *   - Platzhalter (nail_studio_id = NULL)
 *   - eigene Einträge dieses Studios
 *
 * Preis: eigener Studio-Preis (studio_prices) vor Standardpreis.
 */
class PriceService
{
    public function priceList(NailStudio $studio): array
    {
        $overrides = $this->overrides($studio);

        $designs = DesignElement::where('is_active', true)
            ->where(fn (Builder $q) => $this->forStudio($q, $studio))
            ->with('tags:id,name')
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price_per_nail', 'image_path', 'nail_studio_id'])
            ->map(fn (DesignElement $design) => [
                'id'            => $design->id,
                'name'          => $design->name,
                'category'      => $design->category,
                'unit'          => 'nagel',
                'image'         => $design->image_path,
                'tags'          => $design->tags->pluck('name')->all(),
                'default_price' => (float) $design->price_per_nail,
                'price'         => $overrides[DesignElement::class][$design->id]
                                    ?? (float) $design->price_per_nail,
                'is_custom'     => isset($overrides[DesignElement::class][$design->id]),
                'is_own'        => $design->nail_studio_id === $studio->id,
            ])
            ->values();

        $options = PriceOption::where('is_active', true)
            ->where(fn (Builder $q) => $this->forStudio($q, $studio))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (PriceOption $option) => [
                'id'            => $option->id,
                'category'      => $option->category,
                'key'           => $option->key,
                'name'          => $option->name,
                'unit'          => $option->unit,
                'image'         => $option->image_path,
                'default_price' => (float) $option->default_price,
                'price'         => $overrides[PriceOption::class][$option->id]
                                    ?? (float) $option->default_price,
                'is_custom'     => isset($overrides[PriceOption::class][$option->id]),
                'is_own'        => $option->nail_studio_id === $studio->id,
            ])
            ->groupBy('category');

        $colors = Color::where(fn (Builder $q) => $this->forStudio($q, $studio))
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'hex_code', 'category', 'nail_studio_id'])
            ->map(fn (Color $color) => [
                'id'       => $color->id,
                'name'     => $color->name,
                'hex'      => $color->hex_code,
                'category' => $color->category,
                'is_own'   => $color->nail_studio_id === $studio->id,
            ])
            ->values();

        $services = Service::where('nail_studio_id', $studio->id)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'duration_minutes'])
            ->map(fn (Service $service) => [
                'id'               => $service->id,
                'name'             => $service->name,
                'unit'             => 'pauschal',
                'price'            => (float) $service->price,
                'duration_minutes' => $service->duration_minutes,
            ])
            ->values();

        return [
            'designs'  => $designs,
            'options'  => $options,
            'colors'   => $colors,
            'services' => $services,
        ];
    }

    /**
     * NEU: Preis eines gespeicherten Designs in einem bestimmten Studio.
     * Jedes Muster zählt pro Nagel, eigener Studio-Preis vor Standardpreis.
     */
    public function designPrice(NailStudio $studio, Design $design): float
    {
        $overrides = $this->overrides($studio)[DesignElement::class] ?? [];

        $design->loadMissing('nails.designElements');

        $total = 0.0;

        foreach ($design->nails as $nail) {
            foreach ($nail->designElements as $element) {
                $total += $overrides[$element->id] ?? (float) $element->price_per_nail;
            }
        }

        return round($total, 2);
    }

    /**
     * NEU: Was die Kundin für einen Termin zahlt: Leistung + (falls vorhanden) Design.
     * Wird beim Anlegen der Zahlung und für die Anzeige im Checkout benutzt.
     */
    public function appointmentPrice(Appointment $appointment): float
    {
        $appointment->loadMissing('service', 'nailStudio', 'design');

        $price = (float) $appointment->service->price;

        if ($appointment->design) {
            $price += $this->designPrice($appointment->nailStudio, $appointment->design);
        }

        return round($price, 2);
    }

    /** $price = null → eigenen Preis entfernen, Standardpreis gilt wieder */
    public function setPrice(NailStudio $studio, Model $priceable, ?float $price): void
    {
        $match = [
            'nail_studio_id' => $studio->id,
            'priceable_type' => $priceable::class,
            'priceable_id'   => $priceable->getKey(),
        ];

        if ($price === null) {
            StudioPrice::where($match)->delete();
            return;
        }

        StudioPrice::updateOrCreate($match, ['price' => round($price, 2)]);
    }

    /** Platzhalter + eigene Einträge des Studios */
    private function forStudio(Builder $query, NailStudio $studio): void
    {
        $query->whereNull('nail_studio_id')->orWhere('nail_studio_id', $studio->id);
    }

    private function overrides(NailStudio $studio): array
    {
        $result = [];

        foreach ($studio->studioPrices()->get() as $row) {
            $result[$row->priceable_type][$row->priceable_id] = (float) $row->price;
        }

        return $result;
    }
}