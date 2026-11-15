<?php

namespace Database\Factories;

use App\Models\Courier;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'level_id' => Level::query()->inRandomOrder(null)->value('id') ?? 1,
            'is_active' => fake()->boolean(80),
        ];
    }
}
