<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class AllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isEtf = $this->faker->boolean();

        return [
            'user_id' => User::factory(),
            'isin' => $this->faker->unique()->regexify('FR[0-9A-Z]{10}'),
            'ticker' => $this->faker->unique()->bothify('???#.PA'),
            'name' => $this->faker->company . ($isEtf ? ' ETF' : ''),
            'type' => $isEtf ? 'ETF' : 'Action',
            'target_percent' => $isEtf ? $this->faker->randomElement([0, 5, 25, 55]) : 0.0,
        ];
    }
}
