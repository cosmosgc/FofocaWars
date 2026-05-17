<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\War;
use App\Models\Theme;
use App\Game\Map\MapGenerator;
use App\Models\WarPlayer;
use App\Models\Tile;
use Illuminate\Console\Command;

class SeedTestData extends Command
{
    protected $signature = 'game:seed {--users=2 : Number of test users to create}';
    protected $description = 'Seed test users and a war for development';

    public function handle(MapGenerator $generator): void
    {
        $count = (int) $this->option('users');

        $war = War::create([
            'name' => 'Test War',
            'theme' => 'medieval',
            'map_width' => 20,
            'map_height' => 20,
            'status' => 'setup',
        ]);

        $this->info("War '{$war->name}' created.");

        $generator->generate($war);

        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $user = User::factory()->create([
                'name' => "TestUser{$i}",
                'email' => "test{$i}@example.com",
            ]);
            $users[] = $user;

            $this->info("User '{$user->email}' created (password: password).");

            $tile = Tile::where('war_id', $war->id)
                ->whereNull('owner_id')
                ->where('terrain_type', 'plain')
                ->first();

            if (!$tile) {
                $tile = Tile::where('war_id', $war->id)
                    ->whereNull('owner_id')
                    ->first();
            }

            $player = WarPlayer::create([
                'war_id' => $war->id,
                'user_id' => $user->id,
            ]);

            $war->cities()->create([
                'owner_id' => $player->id,
                'name' => "City of {$user->name}",
                'tile_x' => $tile->x,
                'tile_y' => $tile->y,
            ]);

            $tile->update(['owner_id' => $player->id, 'structure_id' => 'city']);
        }

        if ($count >= 1) {
            $first = $users[0];
            $first->is_admin = true;
            $first->save();
            $this->info("User '{$first->email}' promoted to admin.");
        }

        $this->info('Done! Login with test1@example.com / password.');
    }
}
