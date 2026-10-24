<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DesignControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_nail_positions_are_rejected(): void
    {
        $user = User::factory()->create();

        $now = now();

        $nailShapeId = DB::table('nail_shapes')->insertGetId([
            'name' => 'Almond',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $colorId = DB::table('colors')->insertGetId([
            'name' => 'Bordeaux',
            'hex_code' => '#6D1A2B',
            'category' => 'rot', feature/Daniel-integration
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->actingAs($user)->postJson('/designs', [
            'name' => 'Test doppelte Position',
            'nails' => [
                [
                    'nail_position' => 2,
                    'nail_shape_id' => $nailShapeId,
                    'color_id' => $colorId,
                    'element_ids' => [],
                ],
                [
                    'nail_position' => 2,
                    'nail_shape_id' => $nailShapeId,
                    'color_id' => $colorId,
                    'element_ids' => [],
                ],
            ],
        ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'nails.0.nail_position',
            'nails.1.nail_position',
        ]);

        $this->assertDatabaseCount('designs', 0);
    }

    public function test_customer_cannot_view_another_users_design(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $designId = DB::table('designs')->insertGetId([
            'user_id' => $owner->id,
            'name' => 'Privates Design',
            'status' => 'pending',
            'total_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson('/designs/' . $designId);

        $response->assertNotFound();
    }

    public function test_customer_cannot_delete_another_users_design(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $designId = DB::table('designs')->insertGetId([
            'user_id' => $owner->id,
            'name' => 'Privates Design',
            'status' => 'pending',
            'total_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson('/designs/' . $designId);

        $response->assertNotFound();

        $this->assertDatabaseHas('designs', [
            'id' => $designId,
            'user_id' => $owner->id,
        ]);
    }

}
