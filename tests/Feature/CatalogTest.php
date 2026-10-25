<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\DesignElement;
use App\Models\NailStudio;
use App\Models\PriceOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Eigener Katalog pro Studio – nur die Inhaberin darf anlegen und entfernen.
 */
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private NailStudio $studio;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->owner = User::factory()->create(['role' => 'owner']);

        $this->studio = NailStudio::create([
            'name'     => 'Teststudio',
            'address'  => 'Teststraße 1, 38440 Wolfsburg',
            'owner_id' => $this->owner->id,
        ]);
    }

    private function svg(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'muster.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 160"><rect width="100" height="160"/></svg>'
        );
    }

    public function test_inhaberin_legt_muster_mit_bild_an(): void
    {
        $this->actingAs($this->owner)
            ->post(route('catalog.store', $this->studio), [
                'type'     => 'muster',
                'name'     => 'Sternchen',
                'category' => 'Motiv',
                'price'    => 2.5,
                'image'    => $this->svg(),
            ])
            ->assertSessionHasNoErrors();

        $muster = DesignElement::where('name', 'Sternchen')->first();

        $this->assertNotNull($muster);
        $this->assertSame($this->studio->id, $muster->nail_studio_id);
        $this->assertStringStartsWith('/storage/catalog/', $muster->image_path);

        Storage::disk('public')->assertExists(str_replace('/storage/', '', $muster->image_path));
    }

    public function test_muster_ohne_bild_wird_abgelehnt(): void
    {
        $this->actingAs($this->owner)
            ->post(route('catalog.store', $this->studio), [
                'type'     => 'muster',
                'name'     => 'Ohne Bild',
                'category' => 'Muster',
                'price'    => 2,
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('design_elements', ['name' => 'Ohne Bild']);
    }

    public function test_falsches_dateiformat_wird_abgelehnt(): void
    {
        $this->actingAs($this->owner)
            ->post(route('catalog.store', $this->studio), [
                'type'  => 'effekt',
                'name'  => 'Glitzer PDF',
                'price' => 5,
                'image' => UploadedFile::fake()->create('effekt.pdf', 50, 'application/pdf'),
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_inhaberin_legt_farbe_an(): void
    {
        $this->actingAs($this->owner)
            ->post(route('catalog.store', $this->studio), [
                'type'     => 'farbe',
                'name'     => 'Kirschrot',
                'category' => 'rot',
                'hex_code' => '#b0122e',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('colors', [
            'name'           => 'Kirschrot',
            'hex_code'       => '#B0122E',
            'nail_studio_id' => $this->studio->id,
        ]);
    }

    public function test_schichtleitung_darf_nichts_anlegen(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'nail_studio_id' => $this->studio->id]);

        $this->actingAs($admin)
            ->post(route('catalog.store', $this->studio), [
                'type'  => 'material',
                'name'  => 'Builder Gel',
                'price' => 20,
            ])
            ->assertForbidden();
    }

    public function test_inhaberin_entfernt_eigenen_eintrag(): void
    {
        $option = PriceOption::forceCreate([
            'nail_studio_id' => $this->studio->id,
            'category'       => 'material',
            'key'            => 'builder-gel',
            'name'           => 'Builder Gel',
            'default_price'  => 20,
            'unit'           => 'pauschal',
        ]);

        $this->actingAs($this->owner)
            ->delete(route('catalog.destroy', [$this->studio, 'material', $option->id]))
            ->assertRedirect();

        $this->assertModelMissing($option);
    }

    public function test_platzhalter_koennen_nicht_entfernt_werden(): void
    {
        $platzhalter = Color::forceCreate([
            'name'     => 'Platzhalter-Rot',
            'hex_code' => '#FF0000',
            'category' => 'rot',
        ]);

        $this->actingAs($this->owner)
            ->delete(route('catalog.destroy', [$this->studio, 'farbe', $platzhalter->id]))
            ->assertNotFound();

        $this->assertModelExists($platzhalter);
    }

        public function test_muster_bekommt_tags(): void
    {
        $this->actingAs($this->owner)
            ->post(route('catalog.store', $this->studio), [
                'type'     => 'muster',
                'name'     => 'Herbstzauber',
                'category' => 'Motiv',
                'price'    => 3,
                'image'    => $this->svg(),
                'tags'     => 'Herbst, gold , herbst,',
            ])
            ->assertSessionHasNoErrors();

        $tags = DesignElement::where('name', 'Herbstzauber')->first()
            ->tags->pluck('name')->sort()->values()->all();

        // klein geschrieben, ohne Leerzeichen, ohne Doppelte
        $this->assertSame(['gold', 'herbst'], $tags);
    }
}
