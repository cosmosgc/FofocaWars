<?php

namespace Database\Factories;

use App\Models\WarPlayer;
use App\Models\War;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarPlayerFactory extends Factory
{
    protected $model = WarPlayer::class;

    public function definition(): array
    {
        return [
            'war_id' => War::factory(),
            'user_id' => User::factory(),
            'joined_at' => now(),
            'last_active_at' => now(),
        ];
    }
}
