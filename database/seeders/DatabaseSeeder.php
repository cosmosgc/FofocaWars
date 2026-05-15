<?php

namespace Database\Seeders;

use App\Models\War;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        War::create([
            'name' => 'The First War',
            'theme' => 'medieval',
            'map_width' => 100,
            'map_height' => 100,
            'resource_multiplier' => 1.0,
            'troop_speed_multiplier' => 1.0,
            'construction_speed' => 1.0,
            'max_bases_per_player' => 3,
            'status' => 'setup',
        ]);
    }
}
