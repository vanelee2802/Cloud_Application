<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailStudio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
{
    parent::setUp();

    $this->createRoles();
}
    public function test_customer_cannot_create_service(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $user->assignRole('customer');

        $this->actingAs($user)
            ->post('/services')
            ->assertForbidden();
    }

    public function test_employee_can_create_service(): void
    {
        $user = User::factory()->create([
            'role' => 'employee',
        ]);

        $user->assignRole('employee');

        $studio = NailStudio::create([
            'name' => 'Test Studio',
            'address' => 'Teststraße 1',
            'opening_hours' => 'Mo-Fr 9-18 Uhr',
        ]);

        $this->actingAs($user)
            ->post('/services', [
                'nail_studio_id' => $studio->id,
                'name' => 'Test Service',
                'price' => 25,
                'duration_minutes' => 30,
            ])
            ->assertCreated();
    }

    public function test_admin_can_create_service(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $user->assignRole('admin');

        $studio = NailStudio::create([
            'name' => 'Test Studio',
            'address' => 'Teststraße 1',
            'opening_hours' => 'Mo-Fr 9-18 Uhr',
        ]);

        $this->actingAs($user)
            ->post('/services', [
                'nail_studio_id' => $studio->id,
                'name' => 'Admin Service',
                'price' => 30,
                'duration_minutes' => 45,
            ])
            ->assertCreated();
    }
}

