<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Tile;
use App\Game\Map\MapGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarController extends Controller
{
    public function index()
    {
        $wars = War::all();
        return view('wars.index', compact('wars'));
    }

    public function show(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->first();

        return view('wars.show', compact('war', 'player'));
    }

    public function join(War $war, MapGenerator $generator)
    {
        if ($war->status !== 'setup') {
            return back()->with('error', 'This war has already started.');
        }

        $exists = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already joined this war.');
        }

        $isFirst = WarPlayer::where('war_id', $war->id)->count() === 0;

        if ($isFirst) {
            $generator->generate($war);
        }

        \DB::transaction(function () use ($war) {
            $player = WarPlayer::create([
                'war_id' => $war->id,
                'user_id' => Auth::id(),
                'joined_at' => now(),
                'last_active_at' => now(),
            ]);

            $tile = Tile::where('war_id', $war->id)
                ->whereNull('owner_id')
                ->where('terrain_type', 'plain')
                ->inRandomOrder()
                ->first();

            if (!$tile) {
                $tile = Tile::where('war_id', $war->id)
                    ->whereNull('owner_id')
                    ->inRandomOrder()
                    ->first();
            }

            $war->cities()->create([
                'owner_id' => $player->id,
                'name' => 'Capital',
                'tile_x' => $tile->x,
                'tile_y' => $tile->y,
                'population' => 100,
            ]);

            $tile->update(['owner_id' => $player->id, 'structure_id' => 'city']);
        });

        return redirect()->route('wars.show', $war);
    }

    public function start(War $war)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $war->update(['status' => 'running']);
        return back()->with('success', 'War started.');
    }

    public function map(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cities = $war->cities()->where('owner_id', $player->id)->get();

        return view('wars.map', compact('war', 'player', 'cities'));
    }
}
