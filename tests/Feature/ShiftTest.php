<?php

namespace Tests\Feature;

use App\Models\EmployeeShift;
use App\Models\NailStudio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Schichten: Inhaberin und Schichtleitung legen sie fest, sonst niemand.
 */
class ShiftTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private User $employee;
    private NailStudio $studio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);

        $this->studio = NailStudio::create([
            'name'     => 'Teststudio',
            'address'  => 'Teststraße 1, 38440 Wolfsburg',
            'owner_id' => $this->owner->id,
        ]);

        $this->admin = User::factory()->create([
            'role'           => 'admin',
            'nail_studio_id' => $this->studio->id,
        ]);

        $this->employee = User::factory()->create([
            'role'           => 'employee',
            'nail_studio_id' => $this->studio->id,
        ]);
    }

    private function woche(): array
    {
        return [
            'monday'    => ['start' => '09:00', 'end' => '17:00'],
            'tuesday'   => ['start' => '10:00', 'end' => '18:00'],
            'wednesday' => null, // frei
        ];
    }

    public function test_schichtleitung_legt_schichten_fest(): void
    {
        $this->actingAs($this->admin)
            ->put(route('shifts.update', $this->employee), ['shifts' => $this->woche()])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $this->employee->shifts()->count());
        $this->assertDatabaseHas('employee_shifts', [
            'user_id'    => $this->employee->id,
            'weekday'    => 2,
            'start_time' => '10:00',
        ]);
    }

    public function test_inhaberin_legt_schichten_fest(): void
    {
        $this->actingAs($this->owner)
            ->put(route('shifts.update', $this->employee), ['shifts' => $this->woche()])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $this->employee->shifts()->count());
    }

    public function test_speichern_ersetzt_die_alte_woche(): void
    {
        $this->actingAs($this->admin)
            ->put(route('shifts.update', $this->employee), ['shifts' => $this->woche()]);

        $this->actingAs($this->admin)
            ->put(route('shifts.update', $this->employee), ['shifts' => [
                'friday' => ['start' => '08:00', 'end' => '12:00'],
            ]]);

        $this->assertSame([5], $this->employee->shifts()->pluck('weekday')->all());
    }

    public function test_ende_vor_beginn_wird_abgelehnt(): void
    {
        $this->actingAs($this->admin)
            ->put(route('shifts.update', $this->employee), ['shifts' => [
                'monday' => ['start' => '17:00', 'end' => '09:00'],
            ]])
            ->assertSessionHasErrors('shifts.monday.end');

        $this->assertSame(0, EmployeeShift::count());
    }

    public function test_mitarbeiterin_darf_keine_schichten_festlegen(): void
    {
        $this->actingAs($this->employee)
            ->put(route('shifts.update', $this->employee), ['shifts' => $this->woche()])
            ->assertForbidden();
    }

    public function test_schichtleitung_eines_anderen_studios_bekommt_403(): void
    {
        $anderesStudio = NailStudio::create([
            'name'     => 'Anderes Studio',
            'address'  => 'Weg 2, 38440 Wolfsburg',
            'owner_id' => $this->owner->id,
        ]);

        $fremdeLeitung = User::factory()->create([
            'role'           => 'admin',
            'nail_studio_id' => $anderesStudio->id,
        ]);

        $this->actingAs($fremdeLeitung)
            ->put(route('shifts.update', $this->employee), ['shifts' => $this->woche()])
            ->assertForbidden();
    }
}
