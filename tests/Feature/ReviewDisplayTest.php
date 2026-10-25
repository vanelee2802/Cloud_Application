<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Database\Factories\NailStudioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bewertungen auf der Startseite (ohne ausgeblendete) und Ausblenden durch die Inhaberin.
 */
class ReviewDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private NailStudio $studio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner  = User::factory()->create(['role' => 'owner']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $this->owner->id]);
    }

    /** Abgeschlossener Termin + Bewertung */
    private function review(int $rating, array $attributes = [], ?NailStudio $studio = null): Review
    {
        $studio ??= $this->studio;

        $service = Service::create([
            'nail_studio_id'   => $studio->id,
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'price'            => 45,
            'duration_minutes' => 90,
        ]);

        $appointmentId = DB::table('appointments')->insertGetId([
            'nail_studio_id' => $studio->id,
            'service_id'     => $service->id,
            'date'           => now()->subDay()->toDateString(),
            'time'           => '10:00',
            'status'         => 'completed',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return Review::create(array_merge([
            'appointment_id' => $appointmentId,
            'nail_studio_id' => $studio->id,
            'rating'         => $rating,
            'comment'        => 'Super!',
            'author_name'    => 'Lena',
        ], $attributes));
    }

    // ---------- Startseite ----------

    public function test_durchschnitt_und_anzahl_ohne_ausgeblendete(): void
    {
        $this->review(5);
        $this->review(4);
        $this->review(1, ['is_hidden' => true]);

        $this->getJson('/reviews/public')
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('average', 4.5)
            ->assertJsonCount(2, 'reviews');
    }

    public function test_neueste_bewertung_zuerst(): void
    {
        $this->review(3, ['author_name' => 'Alt']);
        $this->review(5, ['author_name' => 'Neu']);

        $this->getJson('/reviews/public')
            ->assertJsonPath('reviews.0.author_name', 'Neu');
    }

    public function test_filter_nach_studio(): void
    {
        $other = NailStudioFactory::new()->create();

        $this->review(5);
        $this->review(2, [], $other);

        $this->getJson("/reviews/public?nail_studio_id={$this->studio->id}")
            ->assertJsonPath('count', 1)
            ->assertJsonPath('average', 5);
    }

    public function test_ohne_bewertungen(): void
    {
        $this->getJson('/reviews/public')
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('average', null)
            ->assertJsonCount(0, 'reviews');
    }

    public function test_limit_wird_begrenzt(): void
    {
        foreach (range(1, 8) as $i) {
            $this->review(5);
        }

        $this->getJson('/reviews/public?limit=3')->assertJsonCount(3, 'reviews');
        $this->getJson('/reviews/public?limit=999')->assertJsonCount(8, 'reviews');
    }

    // ---------- Inhaberin ----------

    public function test_inhaberin_sieht_eigene_bewertungen_auch_ausgeblendete(): void
    {
        $other = NailStudioFactory::new()->create();

        $this->review(5);
        $this->review(1, ['is_hidden' => true]);
        $this->review(4, [], $other);

        $this->actingAs($this->owner)
            ->getJson('/admin/bewertungen/liste')
            ->assertOk()
            ->assertJsonCount(2, 'reviews');
    }

    public function test_inhaberin_blendet_aus_und_wieder_ein(): void
    {
        $review = $this->review(1, ['comment' => 'Unpassender Text']);

        $this->actingAs($this->owner)
            ->patchJson("/admin/bewertungen/{$review->id}", ['is_hidden' => true])
            ->assertOk()
            ->assertJsonPath('is_hidden', true);

        $this->getJson('/reviews/public')->assertJsonPath('count', 0);

        $this->actingAs($this->owner)
            ->patchJson("/admin/bewertungen/{$review->id}", ['is_hidden' => false])
            ->assertOk();

        $this->getJson('/reviews/public')->assertJsonPath('count', 1);
    }

    public function test_fremde_bewertung_kann_nicht_ausgeblendet_werden(): void
    {
        $otherOwner  = User::factory()->create(['role' => 'owner']);
        $otherStudio = NailStudioFactory::new()->create(['owner_id' => $otherOwner->id]);
        $review      = $this->review(5, [], $otherStudio);

        $this->actingAs($this->owner)
            ->patchJson("/admin/bewertungen/{$review->id}", ['is_hidden' => true])
            ->assertForbidden();

        $this->assertFalse($review->fresh()->is_hidden);
    }

    public function test_fremdes_studio_im_filter_ist_verboten(): void
    {
        $otherStudio = NailStudioFactory::new()->create();

        $this->actingAs($this->owner)
            ->getJson("/admin/bewertungen/liste?nail_studio_id={$otherStudio->id}")
            ->assertForbidden();
    }

    public function test_nur_die_inhaberin_darf_verwalten(): void
    {
        $review   = $this->review(5);
        $admin    = User::factory()->create(['role' => 'admin', 'nail_studio_id' => $this->studio->id]);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)
            ->patchJson("/admin/bewertungen/{$review->id}", ['is_hidden' => true])
            ->assertForbidden();

        $this->actingAs($customer)
            ->getJson('/admin/bewertungen/liste')
            ->assertForbidden();
    }

    public function test_verwaltungsseite_laedt_fuer_die_inhaberin(): void
    {
        $this->actingAs($this->owner)
            ->get('/admin/bewertungen')
            ->assertOk();
    }
}
