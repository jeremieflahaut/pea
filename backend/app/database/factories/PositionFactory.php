<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'isin' => $this->faker->unique()->regexify('FR[0-9A-Z]{10}'),
            'name' => $this->faker->company,
            'quantity' => $this->faker->numberBetween(1, 200),
            'current_price' => $this->faker->randomFloat(4, 2, 300),
        ];
    }

    public function withIsin(string $isin, string $name = null): self
    {
        return $this->state(fn () => [
            'isin' => $isin,
            'name' => $name ?? $isin,
        ]);
    }
}
