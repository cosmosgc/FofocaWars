<?php

namespace App\Console\Commands;

use App\Models\War;
use Illuminate\Console\Command;

class CreateWar extends Command
{
    protected $signature = 'game:create-war
        {name : The war name}
        {--theme=medieval : Theme (medieval, modern, space)}
        {--width=100 : Map width}
        {--height=100 : Map height}
        {--resource=1.0 : Resource multiplier}
        {--speed=1.0 : Troop speed multiplier}
        {--construction=1.0 : Construction speed multiplier}
        {--bases=3 : Max bases per player}';

    protected $description = 'Create a new war';

    public function handle(): void
    {
        $war = War::create([
            'name' => $this->argument('name'),
            'theme' => $this->option('theme'),
            'map_width' => (int) $this->option('width'),
            'map_height' => (int) $this->option('height'),
            'resource_multiplier' => (float) $this->option('resource'),
            'troop_speed_multiplier' => (float) $this->option('speed'),
            'construction_speed' => (float) $this->option('construction'),
            'max_bases_per_player' => (int) $this->option('bases'),
            'status' => 'setup',
        ]);

        $this->info("War '{$war->name}' created (ID: {$war->id}).");
    }
}
