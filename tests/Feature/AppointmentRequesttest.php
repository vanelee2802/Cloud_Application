<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Design;
use App\Models\DesignElement;
use App\Models\NailShape;
use App\Models\NailStudio;
use App\Models\Service;
use App\Models\User;
use Database\Factories\NailStudioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Schichtleitung bestätigt Termine für heute:
 * Zahlung = Leistung + Design, und nur Termine des eigenen Studios.
 */
class AppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    private NailStudio $studio;

    private User $admin;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $owner        = User::factory()->create(['role' => 'owner']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $owner->id]);
        $this->admin  = User::factory()->create(['role' => 'admin', 'nail_studio_id' => $this->studio->id]);

        $this->service = Service::create([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'price'            => 35,
            'duration_minutes' => 60,
        ]);
    }

    /** Design mit einem Nagel und einem Muster für 4 € */
    private function makeDesign(): Design
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $shape    = NailShape::forceCreate(['name' => 'Almond']);
        $color    = Color::forceCreate(['name' => 'Rot', 'hex_code' => '#B0122E', 'category' => 'rot']);
        $element  = DesignElement::forceCreate([
            'name'           => 'Herz',
            'category'       => 'Motiv',
            'price_per_nail' => 4,
            'is_active'      => true,
        ]);

        $design = Design::forceCreate([
            'user_id'     => $customer->id,
            'name'        => 'Testdesign',
            'status'      => 'pending',
            'total_price' => 0,
        ]);

        $nail = $design->nails()->forceCreate([
            'nail_position' => 1,
            'nail_shape_id' => $shape->id,
            'color_id'      => $color->id,
        ]);
        $nail->designElements()->attach([$element->id]);

        return $design;
    }

    private function makeRequest(NailStudio $studio, Service $service, ?int $designId = null): int
    {
        return DB::table('appointments')->insertGetId([
            'nail_studio_id' => $studio->id,
            'service_id'     => $service->id,
            'design_id'      => $designId,
            'date'           => now()->toDateString(),
            'time'           => '15:00',
            'status'         => 'requested',
            'customer_name'  => 'Gast',
            'customer_email' => 'gast@example.com',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function test_bestaetigung_berechnet_leistung_plus_design(): void
    {
        $id = $this->makeRequest($this->studio, $this->service, $this->makeDesign()->id);

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/confirm")
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $id,
            'amount'         => 39,
            'status'         => 'pending',
        ]);
    }

    public function test_bestaetigung_ohne_design_berechnet_nur_die_leistung(): void
    {
        $id = $this->makeRequest($this->studio, $this->service);

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/confirm")
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $id,
            'amount'         => 35,
        ]);
    }

    public function test_fremdes_studio_kann_nicht_bestaetigt_werden(): void
    {
        $otherStudio  = NailStudioFactory::new()->create();
        $otherService = Service::create([
            'nail_studio_id'   => $otherStudio->id,
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'price'            => 35,
            'duration_minutes' => 60,
        ]);

        $id = $this->makeRequest($otherStudio, $otherService);

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/confirm")
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'requested']);
    }

    public function test_anfragenliste_zeigt_nur_das_eigene_studio(): void
    {
        $otherStudio  = NailStudioFactory::new()->create();
        $otherService = Service::create([
            'nail_studio_id'   => $otherStudio->id,
            'name'             => 'Nur Maniküre',
            'category'         => 'Maniküre',
            'price'            => 25,
            'duration_minutes' => 30,
        ]);

        $own = $this->makeRequest($this->studio, $this->service);
        $this->makeRequest($otherStudio, $otherService);

        $this->actingAs($this->admin)
            ->getJson('/admin/appointments/requests')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $own);
    }
}