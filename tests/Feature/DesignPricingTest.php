<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\NailShape;
use App\Models\Color;
use App\Models\DesignElement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_design_price_is_calculated_correctly_from_elements(): void
    {
        // Testdaten vorbereiten
        $user = User::factory()->create();
        $shape = NailShape::create(['name' => 'Almond']);
        $color = Color::create(['name' => 'Rot', 'hex_code' => '#E53935']);

        $chrome = DesignElement::create([
            'category' => 'Chrome', 'name' => 'Chrome Effekt', 'price_per_nail' => 3.00,
        ]);
        $glitzer = DesignElement::create([
            'category' => 'Glitzer', 'name' => 'Silber-Glitzer', 'price_per_nail' => 1.00,
        ]);

        // Als Nutzer ein Design mit einem Nagel (Chrome + Glitzer) erstellen
        $response = $this->actingAs($user)->postJson('/designs', [
            'name' => 'Test Design',
            'nails' => [
                [
                    'nail_position' => 1,
                    'nail_shape_id' => $shape->id,
                    'color_id' => $color->id,
                    'element_ids' => [$chrome->id, $glitzer->id],
                ],
            ],
        ]);

        // Prüfen: Anfrage erfolgreich UND Preis korrekt berechnet (3.00 + 1.00 = 4.00)
        $response->assertStatus(201);
        $response->assertJsonPath('total_price', 4);

        $this->assertDatabaseHas('designs', [
            'name' => 'Test Design',
            'total_price' => 4,
        ]);
    }

    public function test_guest_cannot_create_a_design(): void
    {
        // Ohne Login sollte das Erstellen eines Designs verweigert werden
        $response = $this->postJson('/designs', ['name' => 'Test']);

        $response->assertStatus(401);
    }

    
    public function test_design_price_is_calculated_correctly_across_multiple_nails(): void
    {
        $user = User::factory()->create();

        $shape = NailShape::create(['name' => 'Almond']);
        $color = Color::create([
            'name' => 'Rosa',
            'hex_code' => '#FFC0CB',
        ]);

        $chrome = DesignElement::create([
            'category' => 'Chrome',
            'name' => 'Chrome Effekt',
            'price_per_nail' => 3.00,
        ]);

        $glitzer = DesignElement::create([
            'category' => 'Glitzer',
            'name' => 'Glitzer',
            'price_per_nail' => 1.00,
        ]);

        $french = DesignElement::create([
            'category' => 'French',
            'name' => 'French Design',
            'price_per_nail' => 2.00,
        ]);

        $steinchen = DesignElement::create([
            'category' => 'Steinchen',
            'name' => 'Steinchen',
            'price_per_nail' => 1.50,
        ]);

        $response = $this->actingAs($user)->postJson('/designs', [
            'name' => 'Design mit drei Nägeln',
            'nails' => [
                [
                    'nail_position' => 1,
                    'nail_shape_id' => $shape->id,
                    'color_id' => $color->id,
                    'element_ids' => [$chrome->id, $glitzer->id],
                ],
                [
                    'nail_position' => 2,
                    'nail_shape_id' => $shape->id,
                    'color_id' => $color->id,
                    'element_ids' => [$french->id],
                ],
                [
                    'nail_position' => 3,
                    'nail_shape_id' => $shape->id,
                    'color_id' => $color->id,
                    'element_ids' => [$steinchen->id],
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('total_price', 7.5);

        $this->assertDatabaseHas('designs', [
            'name' => 'Design mit drei Nägeln',
            'total_price' => 7.50,
        ]);
    }

    
    public function test_client_cannot_manipulate_design_total_price(): void
    {
        $user = User::factory()->create();

        $shape = NailShape::create(['name' => 'Almond']);
        $color = Color::create([
            'name' => 'Rot',
            'hex_code' => '#E53935',
        ]);

        $chrome = DesignElement::create([
            'category' => 'Chrome',
            'name' => 'Chrome Effekt',
            'price_per_nail' => 3.00,
        ]);

        $response = $this->actingAs($user)->postJson('/designs', [
            'name' => 'Manipulationsversuch',
            'total_price' => 0.01,
            'nails' => [
                [
                    'nail_position' => 1,
                    'nail_shape_id' => $shape->id,
                    'color_id' => $color->id,
                    'element_ids' => [$chrome->id],
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('total_price', 3);

        $this->assertDatabaseHas('designs', [
            'name' => 'Manipulationsversuch',
            'total_price' => 3.00,
        ]);
    }
}