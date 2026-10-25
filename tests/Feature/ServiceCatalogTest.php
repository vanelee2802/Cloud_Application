<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use Database\Factories\NailStudioFactory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function studio(): NailStudio
    {
        $owner = User::factory()->create(['role' => 'owner']);

        return NailStudioFactory::new()->create(['owner_id' => $owner->id]);
    }

    private function service(NailStudio $studio, array $attributes = []): Service
    {
        return Service::create(array_merge([
            'nail_studio_id'   => $studio->id,
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'price'            => 45,
            'duration_minutes' => 90,
        ], $attributes));
    }

    public function test_liefert_aktive_leistungen_des_ersten_studios_sortiert(): void
    {
        $studio = $this->studio();
        $other  = $this->studio();

        $this->service($studio, ['name' => 'B', 'sort_order' => 20]);
        $this->service($studio, ['name' => 'A', 'sort_order' => 10]);
        $this->service($studio, ['name' => 'Versteckt', 'is_active' => false]);
        $this->service($other, ['name' => 'Fremd']);

        $response = $this->getJson('/services/catalog');

        $response->assertOk()
            ->assertJsonPath('studio_id', $studio->id)
            ->assertJsonCount(2, 'studios')
            ->assertJsonCount(2, 'services')
            ->assertJsonPath('services.0.name', 'A')
            ->assertJsonPath('services.1.name', 'B');
    }

    public function test_liefert_das_gewuenschte_studio(): void
    {
        $studio = $this->studio();
        $other  = $this->studio();

        $this->service($studio, ['name' => 'Eins']);
        $this->service($other, ['name' => 'Zwei']);

        $this->getJson("/services/catalog?nail_studio_id={$other->id}")
            ->assertOk()
            ->assertJsonPath('studio_id', $other->id)
            ->assertJsonCount(1, 'services')
            ->assertJsonPath('services.0.name', 'Zwei');
    }

    public function test_unbekanntes_studio_faellt_auf_das_erste_zurueck(): void
    {
        $studio = $this->studio();

        $this->getJson('/services/catalog?nail_studio_id=9999')
            ->assertOk()
            ->assertJsonPath('studio_id', $studio->id);
    }

    public function test_ohne_studios_kommt_eine_leere_liste(): void
    {
        $this->getJson('/services/catalog')
            ->assertOk()
            ->assertJsonPath('studio_id', null)
            ->assertJsonCount(0, 'services');
    }

    public function test_zusatzleistungen_sind_nicht_buchbar(): void
    {
        $studio = $this->studio();

        $this->service($studio, ['name' => 'Haupt', 'sort_order' => 1]);
        $this->service($studio, [
            'name'             => 'Nail Art',
            'category'         => 'Extras',
            'price'            => 5,
            'price_is_from'    => true,
            'duration_minutes' => 0,
            'is_addon'         => true,
            'sort_order'       => 2,
        ]);

        $this->getJson('/services/catalog')
            ->assertJsonPath('services.0.bookable', true)
            ->assertJsonPath('services.1.bookable', false)
            ->assertJsonPath('services.1.price_is_from', true);
    }
}