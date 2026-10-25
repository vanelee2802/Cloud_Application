<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Color;
use App\Models\Design;
use App\Models\DesignElement;
use App\Models\NailShape;
use App\Models\NailStudio;
use App\Models\Service;
use App\Models\User;
use App\Services\PriceService;
use Database\Factories\NailStudioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Design-Preis beim Buchen: Muster pro Nagel, Studio-Preise vor Standardpreisen.
 */
class DesignPriceTest extends TestCase
{
    use RefreshDatabase;

    private NailStudio $studio;

    private DesignElement $glitzer;   // Standard 2 €

    private DesignElement $herz;      // Standard 3 €

    private Design $design;

    protected function setUp(): void
    {
        parent::setUp();

        $owner        = User::factory()->create(['role' => 'owner']);
        $customer     = User::factory()->create(['role' => 'customer']);
        $this->studio = NailStudioFactory::new()->create(['owner_id' => $owner->id]);

        $shape = NailShape::forceCreate(['name' => 'Almond']);
        $color = Color::forceCreate(['name' => 'Rot', 'hex_code' => '#B0122E', 'category' => 'rot']);

        $this->glitzer = DesignElement::forceCreate([
            'name'           => 'Glitzer',
            'category'       => 'Muster',
            'price_per_nail' => 2,
            'is_active'      => true,
        ]);

        $this->herz = DesignElement::forceCreate([
            'name'           => 'Herz',
            'category'       => 'Motiv',
            'price_per_nail' => 3,
            'is_active'      => true,
        ]);

        $this->design = Design::forceCreate([
            'user_id'     => $customer->id,
            'name'        => 'Testdesign',
            'status'      => 'pending',
            'total_price' => 0,
        ]);

        // Daumen: Glitzer + Herz, Zeigefinger: Glitzer → Standard 2 + 3 + 2 = 7 €
        $thumb = $this->design->nails()->forceCreate([
            'nail_position' => 1,
            'nail_shape_id' => $shape->id,
            'color_id'      => $color->id,
        ]);
        $thumb->designElements()->attach([$this->glitzer->id, $this->herz->id]);

        $index = $this->design->nails()->forceCreate([
            'nail_position' => 2,
            'nail_shape_id' => $shape->id,
            'color_id'      => $color->id,
        ]);
        $index->designElements()->attach([$this->glitzer->id]);
    }

    public function test_design_preis_mit_standardpreisen(): void
    {
        $price = app(PriceService::class)->designPrice($this->studio, $this->design);

        $this->assertSame(7.0, $price);
    }

    public function test_eigener_studio_preis_wird_verwendet(): void
    {
        app(PriceService::class)->setPrice($this->studio, $this->glitzer, 1.5);

        // 1,5 + 3 + 1,5
        $price = app(PriceService::class)->designPrice($this->studio->fresh(), $this->design);

        $this->assertSame(6.0, $price);
    }

    public function test_preis_eines_anderen_studios_zaehlt_nicht(): void
    {
        $other = NailStudioFactory::new()->create();

        app(PriceService::class)->setPrice($other, $this->glitzer, 10);

        $price = app(PriceService::class)->designPrice($this->studio, $this->design);

        $this->assertSame(7.0, $price);
    }

    public function test_terminpreis_ist_leistung_plus_design(): void
    {
        $service = Service::create([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Neumodellage Natur',
            'category'         => 'Neumodellage',
            'price'            => 45,
            'duration_minutes' => 90,
        ]);

        $appointmentId = DB::table('appointments')->insertGetId([
            'nail_studio_id' => $this->studio->id,
            'service_id'     => $service->id,
            'design_id'      => $this->design->id,
            'date'           => now()->addDays(3)->toDateString(),
            'time'           => '10:00',
            'status'         => 'held',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $price = app(PriceService::class)->appointmentPrice(Appointment::findOrFail($appointmentId));

        $this->assertSame(52.0, $price);
    }

    public function test_terminpreis_ohne_design_ist_nur_die_leistung(): void
    {
        $service = Service::create([
            'nail_studio_id'   => $this->studio->id,
            'name'             => 'Auffüllen Natur',
            'category'         => 'Auffüllen',
            'price'            => 35,
            'duration_minutes' => 60,
        ]);

        $appointmentId = DB::table('appointments')->insertGetId([
            'nail_studio_id' => $this->studio->id,
            'service_id'     => $service->id,
            'design_id'      => null,
            'date'           => now()->addDays(3)->toDateString(),
            'time'           => '11:00',
            'status'         => 'held',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $price = app(PriceService::class)->appointmentPrice(Appointment::findOrFail($appointmentId));

        $this->assertSame(35.0, $price);
    }
}