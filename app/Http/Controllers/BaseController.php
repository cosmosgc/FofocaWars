<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\Base;
use App\Models\WarPlayer;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    public function show(War $war, Base $base)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($base->war_id !== $war->id || $base->owner_id !== $player->id, 403);

        $typeNames = [
            'resource' => __('Resource Outpost'),
            'military' => __('Troop Camp'),
            'trade'    => __('Trade Post'),
            'alliance' => __('Alliance Base'),
        ];

        $typeColors = [
            'resource' => '#22c55e',
            'military' => '#ef4444',
            'trade'    => '#3b82f6',
            'alliance' => '#a855f7',
        ];

        return view('bases.show', compact('war', 'base', 'player', 'typeNames', 'typeColors'));
    }
}
