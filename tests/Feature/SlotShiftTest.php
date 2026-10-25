<?php

namespace Tests\Feature;

use App\Models\NailStudio;
use App\Models\User;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Freie Plätze richten sich nach den Schichten.
 * Montag, 02.11.2026 dient als Testtag.
 */
class SlotShiftTest extends TestCase
{
    use RefreshDatabase;

    private const MONTAG   = '2026-11-02';
    private const DIENSTAG = '2026-11-03';

    private NailStudio $studio;
    private User $anna;
    private User $bea;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create(['role' => 'owner']);

        $this->studio = NailStudio::create([
            'name'     => 'Teststudio',
            'address'  => 'Teststraße 1, 38440 Wolfsburg',
            'owner_id' => $owner->id,
        ]);

        $this->anna = User::factory()->create(['role' => 'employee', 'nail_studio_id' => $this->studio->id]);
        $this->bea  = User::factory()->create(['role' => 'employee', 'nail_studio_id' => $this->studio->id]);
    }

    private function slots(): SlotService
    {
        // Neue Instanz, damit der Schicht-Zwischenspeicher leer ist
        return new SlotService();
    }

    private function schicht(User $user, int $weekday, string $start, string $end): void
    {
        $user->shifts()->create([
            'weekday'    => $weekday,
            'start_time' => $start,
            'end_time'   => $end,
        ]);
    }

    public function test_ohne_schichten_zaehlen_alle_wie_bisher(): void
    {
        $this->assertSame(2, $this->slots()->capacity($this->studio->id, self::MONTAG, '10:00', 60));
    }

    public function test_nur_wer_schicht_hat_zaehlt(): void
    {
        $this->schicht($this->anna, 1, '09:00', '17:00');   // Montag
        $this->schicht($this->bea,  1, '13:00', '18:00');   // Montag

        $slots = $this->slots();

        $this->assertSame(1, $slots->capacity($this->studio->id, self::MONTAG, '10:00', 60)); // nur Anna
        $this->assertSame(2, $slots->capacity($this->studio->id, self::MONTAG, '14:00', 60)); // beide
    }

    public function test_termin_muss_ganz_in_die_schicht_passen(): void
    {
        $this->schicht($this->anna, 1, '09:00', '17:00');
        $this->schicht($this->bea,  1, '09:00', '18:00');

        // 16:30 + 60 Min. = 17:30 → Anna ist dann schon weg
        $this->assertSame(1, $this->slots()->capacity($this->studio->id, self::MONTAG, '16:30', 60));
    }

    public function test_an_freien_tagen_gibt_es_keine_plaetze(): void
    {
        $this->schicht($this->anna, 1, '09:00', '17:00');   // nur Montag

        $this->assertSame(0, $this->slots()->capacity($this->studio->id, self::DIENSTAG, '10:00', 60));
    }

    public function test_vor_schichtbeginn_gibt_es_keine_plaetze(): void
    {
        $this->schicht($this->anna, 1, '10:00', '17:00');

        $this->assertSame(0, $this->slots()->capacity($this->studio->id, self::MONTAG, '09:00', 30));
    }

    public function test_ohne_dauer_reicht_dass_die_schicht_laeuft(): void
    {
        $this->schicht($this->anna, 1, '09:00', '17:00');

        $slots = $this->slots();

        $this->assertSame(1, $slots->capacity($this->studio->id, self::MONTAG, '16:00'));
        $this->assertSame(0, $slots->capacity($this->studio->id, self::MONTAG, '17:00'));
    }
}
