<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Tile;
use App\Models\Base;
use App\Models\Theme;
use App\Game\Map\MapGenerator;
use App\Game\Theme\ThemeService;
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

        $cityCount = $player ? $war->cities()->where('owner_id', $player->id)->count() : 0;
        $canFoundCity = $player && $cityCount < $war->max_bases_per_player;

        $bases = $player ? Base::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->get() : collect();

        return view('wars.show', compact('war', 'player', 'cityCount', 'canFoundCity', 'bases'));
    }

    public function join(War $war, MapGenerator $generator)
    {
        if ($war->status === 'ended') {
            return back()->with('error', 'This war has ended.');
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

            $this->placeCity($war, $player);
        });

        return redirect()->route('wars.show', $war);
    }

    public function foundCity(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($war->status === 'ended') {
            return back()->with('error', 'This war has ended.');
        }

        $cityCount = $war->cities()->where('owner_id', $player->id)->count();
        if ($cityCount >= $war->max_bases_per_player) {
            return back()->with('error', 'You have reached the maximum number of cities.');
        }

        \DB::transaction(function () use ($war, $player) {
            $this->placeCity($war, $player);
        });

        return redirect()->route('wars.show', $war)->with('success', 'City founded.');
    }

    private function placeCity(War $war, WarPlayer $player): void
    {
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

        $count = $war->cities()->where('owner_id', $player->id)->count();

        $war->cities()->create([
            'owner_id' => $player->id,
            'name' => 'Settlement ' . ($count + 1),
            'tile_x' => $tile->x,
            'tile_y' => $tile->y,
            'population' => 50,
        ]);

        $tile->update(['owner_id' => $player->id, 'structure_id' => 'city']);
    }

    public function start(War $war)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $war->update(['status' => 'running']);
        return back()->with('success', 'War started.');
    }

    public function map(War $war, ThemeService $themeService)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cities = $war->cities()->where('owner_id', $player->id)->get();

        $bases = Base::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->get();

        $playerCityCount = $cities->count();
        $themeColors = $themeService->legendColors($war);
        $themeConfig = $themeService->forWar($war);

        $themeSprites = [];
        foreach (['sprites/terrain/plain', 'sprites/terrain/forest', 'sprites/terrain/mountain', 'sprites/terrain/water', 'sprites/terrain/desert', 'sprites/city'] as $key) {
            if (!empty($themeConfig[$key])) {
                $shortKey = str_replace('sprites/', '', $key);
                $themeSprites[$shortKey] = $themeConfig[$key];
            }
        }

        return view('wars.map', compact('war', 'player', 'cities', 'bases', 'playerCityCount', 'themeColors', 'themeConfig', 'themeSprites'));
    }
}
