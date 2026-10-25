<?php

namespace Database\Factories;

use App\Models\NailStudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Test-Studios für Feature-Tests.
 *
 * Aufruf ohne Änderung am Model: NailStudioFactory::new()->create([...])
 *
 * @extends Factory<NailStudio>
 */
class NailStudioFactory extends Factory
{
    protected $model = NailStudio::class;

    public function definition(): array
    {
        return [
            'name'              => 'Studio ' . fake()->unique()->city(),
            'address'           => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city(),
            'opening_hours'     => null,
            'owner_id'          => null,
            'tax_number'        => null,
            'vat_id'            => null,
            'is_small_business' => false,
            'vat_rate'          => 19,
        ];
    }
}