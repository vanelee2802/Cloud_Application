<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRoles();
    }

    public function test_admin_can_view_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('UserManagement')
                ->has('users')
        );
    }

    public function test_customer_cannot_view_user_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignRole('customer');

        $response = $this->actingAs($customer)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_employee_cannot_view_user_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->assignRole('employee');

        $response = $this->actingAs($employee)->get('/admin/users');

        $response->assertForbidden();
    }

    
    public function test_admin_can_update_user_name_and_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Alter Name',
            'email' => 'alt@example.com',
            'role' => 'customer',
        ]);
        $user->assignRole('customer');

        $response = $this->actingAs($admin)->patch(
            "/admin/users/{$user->id}",
            [
                'name' => 'Neuer Name',
                'email' => 'neu@example.com',
            ]
        );

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
        $customer->assignRole('customer');

        $user = User::factory()->create([
            'role' => 'customer',
        ]);
        $user->assignRole('customer');

        $response = $this->actingAs($customer)->patch(
            "/admin/users/{$user->id}",
            [
                'name' => 'Unberechtigt geändert',
                'email' => 'changed@example.com',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => 'Unberechtigt geändert',
        ]);
    }

    public function test_employee_cannot_update_user(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);
        $employee->assignRole('employee');

        $user = User::factory()->create([
            'role' => 'customer',
        ]);
        $user->assignRole('customer');

        $response = $this->actingAs($employee)->patch(
            "/admin/users/{$user->id}",
            [
                'name' => 'Unberechtigt geändert',
                'email' => 'changed@example.com',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => 'Unberechtigt geändert',
        ]);
    }
}
