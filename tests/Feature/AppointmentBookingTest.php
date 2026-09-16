<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailStudio;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_an_appointment(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id,
            'name' => 'Maniküre',
            'price' => 25,
            'duration_minutes' => 45,
        ]);

        $response = $this->actingAs($user)->postJson('/appointments', [
            'nail_studio_id' => $studio->id,
            'service_id' => $service->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '14:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'status' => 'requested',
        ]);
    }

    public function test_customer_only_sees_their_own_appointments(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);
        $service = Service::create([
            'nail_studio_id' => $studio->id, 'name' => 'Maniküre', 'price' => 25, 'duration_minutes' => 45,
        ]);

        // Termin von Kunde B anlegen
        Appointment::create([
            'user_id' => $customerB->id,
            'nail_studio_id' => $studio->id,
            'service_id' => $service->id,
            'date' => now()->addDay(),
            'time' => '10:00',
            'status' => 'requested',
        ]);

        // Kunde A ruft seine Termine ab -> sollte Kunde B's Termin NICHT sehen
        $response = $this->actingAs($customerA)->getJson('/appointments');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }
}