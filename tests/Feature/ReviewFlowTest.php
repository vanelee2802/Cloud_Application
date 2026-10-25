<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ReviewRequested;
use App\Services\AppointmentCompletion;
use Database\Factories\NailStudioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Nach dem Termin: abschließen → Danke-Mail → Bewertungsformular → eine Bewertung pro Termin.
 */
class ReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    private NailStudio $studio;

    private User $admin;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // Heute 18:00 Uhr
        Carbon::setTestNow(now()->setTime(18, 0));

        $owner        = User::factory()->create(['role' => 'owner']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $owner->id]);
        $this->admin  = User::factory()->create(['role' => 'admin', 'nail_studio_id' => $this->studio->id]);

        $this->service = Service::create([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'price'            => 45,
            'duration_minutes' => 90,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makeAppointment(string $time, string $status = 'confirmed', ?NailStudio $studio = null): int
    {
        $studio ??= $this->studio;

        $serviceId = $studio->is($this->studio)
            ? $this->service->id
            : Service::create([
                'nail_studio_id'   => $studio->id,
                'name'             => 'Auffüllen',
                'category'         => 'Auffüllen',
                'price'            => 35,
                'duration_minutes' => 60,
            ])->id;

        return DB::table('appointments')->insertGetId([
            'nail_studio_id' => $studio->id,
            'service_id'     => $serviceId,
            'date'           => now()->toDateString(),
            'time'           => $time,
            'status'         => $status,
            'customer_name'  => 'Lena Muster',
            'customer_email' => 'lena@example.com',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function createUrl(int $appointmentId): string
    {
        return URL::temporarySignedRoute('reviews.create', now()->addDays(30), ['appointment' => $appointmentId]);
    }

    private function storeUrl(int $appointmentId): string
    {
        return URL::temporarySignedRoute('reviews.store', now()->addHours(2), ['appointment' => $appointmentId]);
    }

    // ---------- Automatisch abschließen ----------

    public function test_vergangene_termine_werden_abgeschlossen_und_bekommen_eine_mail(): void
    {
        $id = $this->makeAppointment('10:00');   // Ende 11:30, jetzt 18:00

        $count = app(AppointmentCompletion::class)->completeFinished();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'completed']);
        $this->assertNotNull(DB::table('appointments')->where('id', $id)->value('review_requested_at'));

        Notification::assertSentOnDemand(
            ReviewRequested::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'lena@example.com'
        );
    }

    public function test_laufende_und_kuenftige_termine_bleiben_offen(): void
    {
        $running = $this->makeAppointment('17:00');   // Ende 18:30
        $future  = $this->makeAppointment('19:00');

        app(AppointmentCompletion::class)->completeFinished();

        $this->assertDatabaseHas('appointments', ['id' => $running, 'status' => 'confirmed']);
        $this->assertDatabaseHas('appointments', ['id' => $future, 'status' => 'confirmed']);
        Notification::assertNothingSent();
    }

    public function test_unbezahlte_termine_werden_nicht_abgeschlossen(): void
    {
        $id = $this->makeAppointment('10:00', 'awaiting_payment');

        app(AppointmentCompletion::class)->completeFinished();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'awaiting_payment']);
    }

    public function test_mail_geht_nur_einmal_raus(): void
    {
        $this->makeAppointment('10:00');

        app(AppointmentCompletion::class)->completeFinished();
        app(AppointmentCompletion::class)->completeFinished();

        Notification::assertSentOnDemandTimes(ReviewRequested::class, 1);
    }

    // ---------- Schichtleitung klickt „Abgeschlossen“ ----------

    public function test_schichtleitung_schliesst_laufenden_termin_ab(): void
    {
        $id = $this->makeAppointment('17:00');

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/complete")
            ->assertOk();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'completed']);
        Notification::assertSentOnDemandTimes(ReviewRequested::class, 1);
    }

    public function test_kuenftiger_termin_kann_nicht_abgeschlossen_werden(): void
    {
        $id = $this->makeAppointment('19:00');

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/complete")
            ->assertUnprocessable();

        $this->assertDatabaseHas('appointments', ['id' => $id, 'status' => 'confirmed']);
    }

    public function test_termin_eines_anderen_studios_ist_verboten(): void
    {
        $other = NailStudioFactory::new()->create();
        $id    = $this->makeAppointment('10:00', 'confirmed', $other);

        $this->actingAs($this->admin)
            ->postJson("/admin/appointments/{$id}/complete")
            ->assertForbidden();
    }

    // ---------- Bewertungsformular ----------

    public function test_formular_braucht_signierten_link(): void
    {
        $id = $this->makeAppointment('10:00', 'completed');

        $this->get("/bewertung/{$id}")->assertForbidden();
        $this->get($this->createUrl($id))->assertOk();
    }

    public function test_formular_nur_fuer_abgeschlossene_termine(): void
    {
        $id = $this->makeAppointment('10:00', 'confirmed');

        $this->get($this->createUrl($id))->assertNotFound();
    }

    public function test_bewertung_wird_gespeichert(): void
    {
        $id = $this->makeAppointment('10:00', 'completed');

        $this->post($this->storeUrl($id), [
            'rating'      => 5,
            'comment'     => 'Wunderschöne Nägel!',
            'author_name' => 'Lena',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'appointment_id' => $id,
            'nail_studio_id' => $this->studio->id,
            'rating'         => 5,
            'author_name'    => 'Lena',
            'is_hidden'      => false,
        ]);
    }

    public function test_nur_eine_bewertung_pro_termin(): void
    {
        $id = $this->makeAppointment('10:00', 'completed');

        $this->post($this->storeUrl($id), ['rating' => 5, 'author_name' => 'Lena']);
        $this->post($this->storeUrl($id), ['rating' => 1, 'author_name' => 'Lena']);

        $this->assertSame(1, Review::where('appointment_id', $id)->count());
        $this->assertSame(5, Review::where('appointment_id', $id)->value('rating'));
    }

    public function test_absenden_ohne_signatur_ist_verboten(): void
    {
        $id = $this->makeAppointment('10:00', 'completed');

        $this->post("/bewertung/{$id}", ['rating' => 5, 'author_name' => 'Lena'])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_sterne_muessen_zwischen_1_und_5_liegen(): void
    {
        $id = $this->makeAppointment('10:00', 'completed');

        $this->postJson($this->storeUrl($id), ['rating' => 6, 'author_name' => 'Lena'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');

        $this->postJson($this->storeUrl($id), ['rating' => 4])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('author_name');
    }
}