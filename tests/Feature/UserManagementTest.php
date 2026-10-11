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
}
