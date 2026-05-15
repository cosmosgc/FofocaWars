<?php

namespace Database\Factories;

use App\Models\War;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarFactory extends Factory
{
    protected $model = War::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'theme' => fake()->randomElement(['medieval', 'modern', 'space']),
            'map_width' => 5,
            'map_height' => 5,
            'resource_multiplier' => 1.0,
            'troop_speed_multiplier' => 1.0,
            'construction_speed' => 1.0,
            'max_bases_per_player' => 3,
            'status' => 'setup',
        ];
    }
}
