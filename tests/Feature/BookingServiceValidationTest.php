<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use Database\Factories\NailStudioFactory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Buchung aus dem Warenkorb: Nur buchbare Leistungen des Studios,
 * Designs nur von ihrer Besitzerin.
 */
class BookingServiceValidationTest extends TestCase
{
    use RefreshDatabase;

    private NailStudio $studio;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $owner        = User::factory()->create(['role' => 'owner']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $owner->id]);

        $this->service = $this->makeService($this->studio);
    }

    private function makeService(NailStudio $studio, array $attributes = []): Service
    {
        return Service::create(array_merge([
            'nail_studio_id'   => $studio->id,
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'price'            => 45,
            'duration_minutes' => 90,
        ], $attributes));
    }

    private function holdPayload(array $overrides = []): array
    {
        return array_merge([
            'nail_studio_id' => $this->studio->id,
            'service_id'     => $this->service->id,
            'design_id'      => null,
            'date'           => now()->addDays(3)->toDateString(),
            'time'           => config('booking.times')[0],
        ], $overrides);
    }

    /** Legt ein Design für diese Person an (nur die nötigsten Spalten) */
    private function makeDesign(User $user): int
    {
        return DB::table('designs')->insertGetId([
            'user_id'    => $user->id,
            'name'       => 'Mein Design',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_buchbare_leistung_wird_akzeptiert(): void
    {
        // 201 (reserviert) oder 422 „ausgebucht“, falls keine Schicht – aber kein Fehler zur Leistung
        $this->postJson('/appointments/hold', $this->holdPayload())
            ->assertJsonMissingValidationErrors(['service_id', 'design_id']);
    }

    public function test_zusatzleistung_kann_nicht_gebucht_werden(): void
    {
        $addon = $this->makeService($this->studio, [
            'name'             => 'Nail Art',
            'category'         => 'Extras',
            'duration_minutes' => 0,
            'is_addon'         => true,
        ]);

        $this->postJson('/appointments/hold', $this->holdPayload(['service_id' => $addon->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    public function test_ausgeblendete_leistung_kann_nicht_gebucht_werden(): void
    {
        $this->service->update(['is_active' => false]);

        $this->postJson('/appointments/hold', $this->holdPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    public function test_leistung_eines_anderen_studios_wird_abgelehnt(): void
    {
        $otherOwner  = User::factory()->create(['role' => 'owner']);
        $otherStudio = NailStudioFactory::new()->create(['owner_id' => $otherOwner->id]);
        $foreign     = $this->makeService($otherStudio);

        $this->postJson('/appointments/hold', $this->holdPayload(['service_id' => $foreign->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    public function test_freie_zeiten_nur_fuer_buchbare_leistungen(): void
    {
        $addon = $this->makeService($this->studio, [
            'name'             => 'French Design',
            'duration_minutes' => 15,
            'is_addon'         => true,
        ]);

        $query = http_build_query([
            'nail_studio_id' => $this->studio->id,
            'date'           => now()->addDays(3)->toDateString(),
            'service_id'     => $addon->id,
        ]);

        $this->getJson("/appointments/availability?{$query}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    public function test_eigenes_design_darf_mitgebucht_werden(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $designId = $this->makeDesign($customer);

        $this->actingAs($customer)
            ->postJson('/appointments/hold', $this->holdPayload(['design_id' => $designId]))
            ->assertJsonMissingValidationErrors(['service_id', 'design_id']);
    }

    public function test_fremdes_design_wird_abgelehnt(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other    = User::factory()->create(['role' => 'customer']);
        $designId = $this->makeDesign($other);

        $this->actingAs($customer)
            ->postJson('/appointments/hold', $this->holdPayload(['design_id' => $designId]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('design_id');
    }

    public function test_gast_kann_kein_design_mitbuchen(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $designId = $this->makeDesign($customer);

        $this->postJson('/appointments/hold', $this->holdPayload(['design_id' => $designId]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('design_id');
    }
}