<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use App\Models\Service;
use App\Models\User;
use Database\Factories\NailStudioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Mitarbeiterinnen sehen die gebuchten Termine ihres Studios,
 * bearbeiten und abschließen bleibt Sache der Schichtleitung.
 */
class EmployeeBookingsTest extends TestCase
{
    use RefreshDatabase;

    private NailStudio $studio;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $owner          = User::factory()->create(['role' => 'owner']);
        $this->studio   = NailStudioFactory::new()->create(['owner_id' => $owner->id]);
        $this->employee = User::factory()->create(['role' => 'employee', 'nail_studio_id' => $this->studio->id]);
    }

    private function booking(NailStudio $studio, string $status = 'confirmed'): int
    {
        $service = Service::create([
            'nail_studio_id'   => $studio->id,
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'price'            => 35,
            'duration_minutes' => 60,
        ]);

        return DB::table('appointments')->insertGetId([
            'nail_studio_id' => $studio->id,
            'service_id'     => $service->id,
            'date'           => now()->addDay()->toDateString(),
            'time'           => '10:00',
            'status'         => $status,
            'customer_name'  => 'Lena',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function test_mitarbeiterin_sieht_buchungen_ihres_studios(): void
    {
        $own   = $this->booking($this->studio);
        $other = NailStudioFactory::new()->create();
        $this->booking($other);

        $this->actingAs($this->employee)
            ->getJson('/admin/appointments/bookings')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $own);
    }

    public function test_mitarbeiterin_darf_nicht_abschliessen(): void
    {
        $id = $this->booking($this->studio);

        $this->actingAs($this->employee)
            ->postJson("/admin/appointments/{$id}/complete")
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'confirmed']);
    }

    public function test_mitarbeiterin_sieht_keine_anfragen(): void
    {
        $this->actingAs($this->employee)
            ->getJson('/admin/appointments/requests')
            ->assertForbidden();
    }

    public function test_kundin_sieht_keine_buchungen(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->getJson('/admin/appointments/bookings')
            ->assertForbidden();
    }
}
