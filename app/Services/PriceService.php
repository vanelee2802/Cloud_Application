<?php

namespace App\Services;

use App\Models\DesignElement;
use App\Models\NailStudio;
use App\Models\PriceOption;
use App\Models\Service;
use App\Models\StudioPrice;
use Illuminate\Database\Eloquent\Model;

/**
 * Preise pro Studio.
 *
 * Regel: Hat das Studio einen eigenen Preis (studio_prices), gilt dieser.
 *        Sonst gilt der Standardpreis (design_elements.price_per_nail
 *        bzw. price_options.default_price).
 *
 * Dienstleistungen (services) gehören schon zu einem Studio und
 * haben ihren Preis direkt in der eigenen Spalte "price".
 */
class PriceService
{
    /**
     * Komplette Preisliste eines Studios – für den Editor und die Preis-Seite.
     */
    public function priceList(NailStudio $studio): array
    {
        $overrides = $this->overrides($studio);

        // Muster: Standardpreis + ggf. Studio-Preis
        $designs = DesignElement::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'price_per_nail'])
            ->map(fn (DesignElement $design) => [
                'id'            => $design->id,
                'name'          => $design->name,
                'category'      => $design->category,
                'unit'          => 'nagel',
                'default_price' => (float) $design->price_per_nail,
                'price'         => $overrides[DesignElement::class][$design->id]
                                    ?? (float) $design->price_per_nail,
                'is_custom'     => isset($overrides[DesignElement::class][$design->id]),
            ])
            ->values();

        // Effekte, Verzierungen, Längen, Materialien
        $options = PriceOption::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PriceOption $option) => [
                'id'            => $option->id,
                'category'      => $option->category,
                'key'           => $option->key,
                'name'          => $option->name,
                'unit'          => $option->unit,
                'default_price' => (float) $option->default_price,
                'price'         => $overrides[PriceOption::class][$option->id]
                                    ?? (float) $option->default_price,
                'is_custom'     => isset($overrides[PriceOption::class][$option->id]),
            ])
            ->groupBy('category');

        // Dienstleistungen: gehören direkt zum Studio
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
            'services' => $services,
        ];
    }

    /**
     * Preis für ein Muster oder eine Option in einem Studio setzen.
     * $price = null → eigenen Preis entfernen, Standardpreis gilt wieder.
     */
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

    /**
     * Alle eigenen Preise eines Studios als
     * [Klassenname => [id => preis]]
     */
    private function overrides(NailStudio $studio): array
    {
        $result = [];

        foreach ($studio->studioPrices()->get() as $row) {
            $result[$row->priceable_type][$row->priceable_id] = (float) $row->price;
        }

        return $result;
    }
}