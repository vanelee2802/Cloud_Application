<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailStudio;
use App\Models\Service;
use App\Models\Appointment;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_an_appointment(): void
    {
        // Stripe im Test ersetzen, damit keine echte Zahlung gestartet wird
        $this->mock(StripeCheckoutService::class, function ($mock) {
            $mock->shouldReceive('createCheckoutUrl')->andReturn('https://checkout.test');
        });

        $user = User::factory()->create(['role' => 'customer']);
        $studio = NailStudio::create(['name' => 'Test Studio', 'address' => 'Teststraße 1']);

        // Eine Mitarbeiterin = ein freier Platz pro Uhrzeit
        User::factory()->create(['role' => 'employee', 'nail_studio_id' => $studio->id]);

        $service = Service::create([
            'nail_studio_id' => $studio->id,
            'name' => 'Maniküre',
            'price' => 25,
            'duration_minutes' => 45,
        ]);

        // Schritt 1: Termin reservieren
        $hold = $this->actingAs($user)->postJson('/appointments/hold', [
            'nail_studio_id' => $studio->id,
            'service_id' => $service->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => collect(config('booking.times'))->first(),
        ]);

        $hold->assertStatus(201);
        $appointmentId = $hold->json('id');

        // Schritt 2: Reservierung abschicken
        $submit = $this->actingAs($user)
            ->withHeader('X-Hold-Token', $hold->json('hold_token'))
            ->postJson("/appointments/{$appointmentId}/submit");

        $submit->assertStatus(200);
        $submit->assertJson([
            'status' => 'awaiting_payment',
            'checkout_url' => 'https://checkout.test',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'user_id' => $user->id,
            'status' => 'awaiting_payment',
        ]);

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointmentId,
            'status' => 'pending',
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