<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\War;
use App\Models\WarPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        $war = War::factory();
        $player = WarPlayer::factory();

        return [
            'war_id' => $war,
            'owner_id' => $player,
            'name' => fake()->city(),
            'tile_x' => fake()->numberBetween(0, 50),
            'tile_y' => fake()->numberBetween(0, 50),
            'population' => 100,
        ];
    }
}
