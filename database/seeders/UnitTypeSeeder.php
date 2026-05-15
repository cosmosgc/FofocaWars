<?php

namespace Database\Seeders;

use App\Models\UnitType;
use Illuminate\Database\Seeder;

class UnitTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Spearman', 'role' => 'infantry', 'attack' => 8, 'defense' => 5, 'speed' => 12, 'wood_cost' => 30, 'stone_cost' => 10, 'food_cost' => 20, 'metal_cost' => 10, 'population_cost' => 1, 'tier' => 1, 'training_time' => 2],
            ['name' => 'Swordsman', 'role' => 'infantry', 'attack' => 15, 'defense' => 12, 'speed' => 10, 'wood_cost' => 50, 'stone_cost' => 20, 'food_cost' => 30, 'metal_cost' => 30, 'population_cost' => 1, 'tier' => 2, 'training_time' => 3],
            ['name' => 'Archer', 'role' => 'ranged', 'attack' => 18, 'defense' => 4, 'speed' => 10, 'wood_cost' => 60, 'stone_cost' => 10, 'food_cost' => 20, 'metal_cost' => 10, 'population_cost' => 1, 'tier' => 1, 'training_time' => 3],
            ['name' => 'Cavalry', 'role' => 'mounted', 'attack' => 20, 'defense' => 8, 'speed' => 18, 'wood_cost' => 40, 'stone_cost' => 10, 'food_cost' => 60, 'metal_cost' => 20, 'population_cost' => 2, 'tier' => 2, 'training_time' => 5],
            ['name' => 'Catapult', 'role' => 'siege', 'attack' => 40, 'defense' => 2, 'speed' => 4, 'wood_cost' => 150, 'stone_cost' => 100, 'food_cost' => 30, 'metal_cost' => 80, 'population_cost' => 4, 'tier' => 3, 'training_time' => 10],
            ['name' => 'Scout', 'role' => 'scout', 'attack' => 2, 'defense' => 2, 'speed' => 24, 'wood_cost' => 20, 'stone_cost' => 5, 'food_cost' => 15, 'metal_cost' => 5, 'population_cost' => 1, 'tier' => 1, 'training_time' => 1],
        ];

        foreach ($types as $type) {
            UnitType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
