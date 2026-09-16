<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailStudio;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_delete_a_service(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);

        $response = $this->actingAs($customer)->deleteJson("/services/{$service->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_employee_can_delete_a_service(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);

        $response = $this->actingAs($employee)->deleteJson("/services/{$service->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_everyone_can_view_services(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);

        $response = $this->actingAs($customer)->getJson('/services');

        $response->assertStatus(200);
    }
}