<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
        ]);

        $response = $this->actingAs($owner)
            ->get('/admin/users');

        $response->assertOk();
    }

    public function test_customer_cannot_view_user_management(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_employee_cannot_view_user_management(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_can_update_user(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
        ]);

        $user = User::factory()->create([
            'name' => 'Alter Name',
            'email' => 'kunde@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($owner)
            ->patch("/admin/users/{$user->id}", [
                'name' => 'Neuer Name',
                'email' => 'neu@example.com',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Neuer Name',
            'email' => 'neu@example.com',
        ]);
    }

    public function test_customer_cannot_update_user(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $user = User::factory()->create([
            'name' => 'Unverändert',
            'email' => 'kunde@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)
            ->patch("/admin/users/{$user->id}", [
                'name' => 'Manipulierter Name',
                'email' => 'manipuliert@example.com',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Unverändert',
            'email' => 'kunde@example.com',
        ]);
    }

    public function test_employee_cannot_update_user(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $user = User::factory()->create([
            'name' => 'Unverändert',
            'email' => 'kunde@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($employee)
            ->patch("/admin/users/{$user->id}", [
                'name' => 'Manipulierter Name',
                'email' => 'manipuliert@example.com',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Unverändert',
            'email' => 'kunde@example.com',
        ]);
    }
}
