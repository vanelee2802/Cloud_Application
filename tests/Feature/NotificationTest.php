<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailStudio;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Design;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_gets_notified_when_appointment_is_confirmed(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $employee = User::factory()->create(['role' => 'employee']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);
        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'nail_studio_id' => $studio->id,
            'service_id' => $service->id,
            'date' => now()->addDay(),
            'time' => '14:00',
            'status' => 'requested',
        ]);

        $response = $this->actingAs($employee)
            ->patchJson("/appointments/{$appointment->id}", ['status' => 'confirmed']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'appointment_status_changed',
            'message' => 'Dein Termin wurde bestätigt.',
        ]);
    }

    public function test_customer_gets_notified_when_design_is_rejected(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $employee = User::factory()->create(['role' => 'employee']);
        $design = Design::create([
            'user_id' => $customer->id,
            'name' => 'Test Design',
            'status' => 'pending',
            'total_price' => 10,
        ]);

        $response = $this->actingAs($employee)
            ->patchJson("/designs/{$design->id}", ['status' => 'rejected']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'design_status_changed',
            'message' => 'Dein Design wurde leider abgelehnt.',
        ]);
    }

    public function test_no_notification_when_status_is_unchanged(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $employee = User::factory()->create(['role' => 'employee']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);
        $appointment = Appointment::create([
            'user_id' => $customer->id,
            'nail_studio_id' => $studio->id,
            'service_id' => $service->id,
            'date' => now()->addDay(),
            'time' => '14:00',
            'status' => 'requested',
        ]);

        $this->actingAs($employee)
            ->patchJson("/appointments/{$appointment->id}", ['status' => 'requested']);

        $this->assertDatabaseCount('notifications', 0);
    }
}