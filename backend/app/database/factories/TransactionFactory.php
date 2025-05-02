<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class TransactionFactory extends Factory
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
            'isin' => $this->faker->regexify('FR[0-9A-Z]{10}'),
            'quantity' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(4, 10, 500),
            'type' => $this->faker->randomElement(['buy', 'sell']),
            'date' => $this->faker->date(),
        ];
    }
}
