<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Design;
use App\Models\DesignElement;
use App\Models\NailShape;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_design_with_nails_and_elements(): void
    {
        $user = User::factory()->create();

        $almond = NailShape::create([
            'name' => 'Almond',
        ]);

        $square = NailShape::create([
            'name' => 'Square',
        ]);

        $red = Color::create([
            'name' => 'Rot',
            'hex_code' => '#E53935',
        ]);

        $pink = Color::create([
            'name' => 'Rosa',
            'hex_code' => '#F48FB1',
        ]);

        $chrome = DesignElement::create([
            'category' => 'Chrome',
            'name' => 'Chrome Effekt',
            'price_per_nail' => 3.00,
        ]);

        $french = DesignElement::create([
            'category' => 'French',
            'name' => 'Klassisch French',
            'price_per_nail' => 2.00,
        ]);

        $response = $this->actingAs($user)->postJson('/designs', [
            'name' => 'Mein Design',
            'nails' => [
                [
                    'nail_position' => 1,
                    'nail_shape_id' => $almond->id,
                    'color_id' => $red->id,
                    'element_ids' => [$chrome->id],
                ],
                [
                    'nail_position' => 2,
                    'nail_shape_id' => $square->id,
                    'color_id' => $pink->id,
                    'element_ids' => [$french->id],
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Mein Design')
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('total_price', 5);

        $this->assertDatabaseHas('designs', [
            'user_id' => $user->id,
            'name' => 'Mein Design',
            'status' => 'pending',
            'total_price' => 5.00,
        ]);

        $this->assertDatabaseCount('design_nails', 2);
        $this->assertDatabaseCount('design_nail_elements', 2);
    }

    public function test_user_cannot_view_another_users_design(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $design = Design::create([
            'user_id' => $owner->id,
            'name' => 'Privates Design',
            'status' => 'pending',
            'total_price' => 0,
        ]);

        $this->actingAs($otherUser)
            ->getJson('/designs/' . $design->id)
            ->assertNotFound();
    }

    public function test_user_cannot_update_another_users_design(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $design = Design::create([
            'user_id' => $owner->id,
            'name' => 'Original',
            'status' => 'pending',
            'total_price' => 0,
        ]);

        $this->actingAs($otherUser)
            ->patchJson('/designs/' . $design->id, [
                'name' => 'Manipuliert',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('designs', [
            'id' => $design->id,
            'user_id' => $owner->id,
            'name' => 'Original',
        ]);
    }

    public function test_user_cannot_delete_another_users_design(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $design = Design::create([
            'user_id' => $owner->id,
            'name' => 'Privates Design',
            'status' => 'pending',
            'total_price' => 0,
        ]);

        $this->actingAs($otherUser)
            ->deleteJson('/designs/' . $design->id)
            ->assertNotFound();

        $this->assertDatabaseHas('designs', [
            'id' => $design->id,
            'user_id' => $owner->id,
            'name' => 'Privates Design',
        ]);
    }
    
    public function test_user_can_only_see_own_designs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Design::create([
            'user_id' => $user->id,
            'name' => 'Mein Design',
            'status' => 'pending',
            'total_price' => 10.00,
        ]);

        Design::create([
            'user_id' => $otherUser->id,
            'name' => 'Fremdes Design',
            'status' => 'pending',
            'total_price' => 20.00,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/designs')
            ->assertOk();

        $response->assertJsonCount(1);
        $response->assertJsonPath('0.name', 'Mein Design');
    }
    
    public function test_invalid_nail_shape_is_rejected(): void
    {
        $user = User::factory()->create();

        $color = Color::create([
            'name' => 'Rot',
            'hex_code' => '#E53935',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/designs', [
                'name' => 'Ungültiges Design',
                'nails' => [
                    [
                        'nail_position' => 1,
                        'nail_shape_id' => 99999,
                        'color_id' => $color->id,
                        'element_ids' => [],
                    ],
                ],
            ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('designs', [
            'name' => 'Ungültiges Design',
        ]);
    }
    
    public function test_invalid_color_is_rejected(): void
    {
        $user = User::factory()->create();

        $shape = NailShape::create([
            'name' => 'Almond',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/designs', [
                'name' => 'Ungültige Farbe',
                'nails' => [
                    [
                        'nail_position' => 1,
                        'nail_shape_id' => $shape->id,
                        'color_id' => 99999,
                        'element_ids' => [],
                    ],
                ],
            ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('designs', [
            'name' => 'Ungültige Farbe',
        ]);
    }
    
    public function test_invalid_design_element_is_rejected(): void
    {
        $user = User::factory()->create();

        $shape = NailShape::create([
            'name' => 'Almond',
        ]);

        $color = Color::create([
            'name' => 'Rot',
            'hex_code' => '#E53935',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/designs', [
                'name' => 'Ungültiges Element',
                'nails' => [
                    [
                        'nail_position' => 1,
                        'nail_shape_id' => $shape->id,
                        'color_id' => $color->id,
                        'element_ids' => [99999],
                    ],
                ],
            ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('designs', [
            'name' => 'Ungültiges Element',
        ]);
    }
}




