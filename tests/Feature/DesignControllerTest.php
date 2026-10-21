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
            'category' => 'rot',
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
}
