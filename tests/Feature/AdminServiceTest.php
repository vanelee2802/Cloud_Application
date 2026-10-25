<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use Database\Factories\NailStudioFactory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private NailStudio $studio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create(['role' => 'owner']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $this->owner->id]);
    }

    private function service(NailStudio $studio, array $attributes = []): Service
    {
        return Service::create(array_merge([
            'nail_studio_id'   => $studio->id,
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'price'            => 35,
            'duration_minutes' => 60,
        ], $attributes));
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Neumodellage French',
            'category'         => 'Neumodellage',
            'description'      => 'Mit klassischen French-Spitzen.',
            'price'            => 55,
            'duration_minutes' => 120,
            'is_popular'       => true,
        ], $overrides);
    }

    public function test_inhaberin_sieht_alle_leistungen_ihres_studios_auch_ausgeblendete(): void
    {
        $this->service($this->studio, ['name' => 'Sichtbar']);
        $this->service($this->studio, ['name' => 'Aus', 'is_active' => false]);

        $this->actingAs($this->owner)
            ->getJson("/admin/services?nail_studio_id={$this->studio->id}")
            ->assertOk()
            ->assertJsonCount(2, 'services');
    }

    public function test_inhaberin_sieht_keine_fremden_studios(): void
    {
        $otherOwner  = User::factory()->create(['role' => 'owner']);
        $otherStudio = NailStudioFactory::new()->create(['owner_id' => $otherOwner->id]);

        $this->actingAs($this->owner)
            ->getJson("/admin/services?nail_studio_id={$otherStudio->id}")
            ->assertForbidden();
    }

    public function test_kundin_und_gast_kommen_nicht_rein(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->getJson("/admin/services?nail_studio_id={$this->studio->id}")
            ->assertForbidden();

        auth()->logout();

        $this->getJson("/admin/services?nail_studio_id={$this->studio->id}")
            ->assertUnauthorized();
    }

    public function test_inhaberin_legt_leistung_an(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/admin/services', $this->validData())
            ->assertCreated()
            ->assertJsonPath('service.name', 'Neumodellage French')
            ->assertJsonPath('service.is_popular', true)
            ->assertJsonPath('service.is_active', true);

        $this->assertDatabaseHas('services', [
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Neumodellage French',
            'duration_minutes' => 120,
        ]);
    }

    public function test_leistung_fuer_fremdes_studio_wird_abgelehnt(): void
    {
        $otherOwner  = User::factory()->create(['role' => 'owner']);
        $otherStudio = NailStudioFactory::new()->create(['owner_id' => $otherOwner->id]);

        $this->actingAs($this->owner)
            ->postJson('/admin/services', $this->validData(['nail_studio_id' => $otherStudio->id]))
            ->assertForbidden();

        $this->assertDatabaseCount('services', 0);
    }

    public function test_buchbare_leistung_braucht_eine_dauer(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/admin/services', $this->validData(['duration_minutes' => 0]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('duration_minutes');
    }

    public function test_zusatzleistung_darf_dauer_null_haben(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/admin/services', $this->validData([
                'name'             => 'Nail Art',
                'category'         => 'Extras',
                'price'            => 5,
                'price_is_from'    => true,
                'duration_minutes' => 0,
                'is_addon'         => true,
            ]))
            ->assertCreated()
            ->assertJsonPath('service.bookable', false);
    }

    public function test_pflichtfelder_werden_geprueft(): void
    {
        $this->actingAs($this->owner)
            ->postJson('/admin/services', ['nail_studio_id' => $this->studio->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category', 'price', 'duration_minutes']);
    }

    public function test_inhaberin_aendert_leistung(): void
    {
        $service = $this->service($this->studio);

        $this->actingAs($this->owner)
            ->patchJson("/admin/services/{$service->id}", $this->validData([
                'name'      => 'Auffüllen Natur',
                'price'     => 38.5,
                'is_active' => false,
            ]))
            ->assertOk()
            ->assertJsonPath('service.price', 38.5)
            ->assertJsonPath('service.is_active', false);
    }

    public function test_fremde_leistung_kann_nicht_geaendert_werden(): void
    {
        $otherOwner  = User::factory()->create(['role' => 'owner']);
        $otherStudio = NailStudioFactory::new()->create(['owner_id' => $otherOwner->id]);
        $service     = $this->service($otherStudio);

        $this->actingAs($this->owner)
            ->patchJson("/admin/services/{$service->id}", $this->validData())
            ->assertForbidden();
    }

    public function test_leistung_ohne_termine_wird_geloescht(): void
    {
        $service = $this->service($this->studio);

        $this->actingAs($this->owner)
            ->deleteJson("/admin/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('archived', false);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_leistung_mit_terminen_wird_nur_ausgeblendet(): void
    {
        $service = $this->service($this->studio);

        DB::table('appointments')->insert([
            'nail_studio_id' => $this->studio->id,
            'service_id'     => $service->id,
            'date'           => now()->addDay()->toDateString(),
            'time'           => '10:00',
            'status'         => 'confirmed',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->actingAs($this->owner)
            ->deleteJson("/admin/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('archived', true);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => false]);
    }
}