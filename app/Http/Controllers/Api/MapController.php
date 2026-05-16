<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\Tile;
use App\Models\City;
use App\Models\WarPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function tiles(War $war, Request $request)
    {
        $query = Tile::where('war_id', $war->id);

        if ($request->has('x_from')) {
            $query->where('x', '>=', (int) $request->x_from);
        }
        if ($request->has('x_to')) {
            $query->where('x', '<=', (int) $request->x_to);
        }
        if ($request->has('y_from')) {
            $query->where('y', '>=', (int) $request->y_from);
        }
        if ($request->has('y_to')) {
            $query->where('y', '<=', (int) $request->y_to);
        }

        $tiles = $query->get(['x', 'y', 'terrain_type', 'owner_id', 'resource_type', 'structure_id']);

        return response()->json($tiles);
    }

    public function cities(War $war)
    {
        $cities = $war->cities()
            ->with('owner.user')
            ->get(['id', 'owner_id', 'name', 'tile_x', 'tile_y', 'population', 'wood', 'stone', 'food', 'metal']);

        return response()->json($cities->map(fn($c) => [
            'id' => $c->id,
            'owner_id' => $c->owner_id,
            'owner_name' => $c->owner?->user?->name,
            'name' => $c->name,
            'tile_x' => $c->tile_x,
            'tile_y' => $c->tile_y,
            'population' => $c->population,
            'wood' => $c->wood,
            'stone' => $c->stone,
            'food' => $c->food,
            'metal' => $c->metal,
        ]));
    }

    public function foundOnTile(War $war, Request $request)
    {
        $request->validate([
            'x' => 'required|integer|min:0',
            'y' => 'required|integer|min:0',
        ]);

        if ($war->status === 'ended') {
            return response()->json(['error' => 'War has ended.'], 400);
        }

        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tile = Tile::where('war_id', $war->id)
            ->where('x', $request->x)
            ->where('y', $request->y)
            ->firstOrFail();

        if ($tile->owner_id) {
            return response()->json(['error' => 'Tile already owned.'], 400);
        }

        if ($tile->terrain_type === 'water') {
            return response()->json(['error' => 'Cannot found on water.'], 400);
        }

        $cityCount = $war->cities()->where('owner_id', $player->id)->count();
        if ($cityCount >= $war->max_bases_per_player) {
            return response()->json(['error' => 'Maximum cities reached.'], 400);
        }

        if ($cityCount > 0) {
            $costScale = max(1, (float) $war->construction_speed);
            $costs = [
                'wood' => (int) round(200 * $costScale),
                'stone' => (int) round(150 * $costScale),
                'food' => (int) round(100 * $costScale),
                'metal' => (int) round(50 * $costScale),
            ];

            $playerCities = City::where('war_id', $war->id)
                ->where('owner_id', $player->id)
                ->get();

            foreach (['wood', 'stone', 'food', 'metal'] as $r) {
                $total = $playerCities->sum($r);
                if ($total < $costs[$r]) {
                    return response()->json([
                        'error' => "Not enough {$r}. Need {$costs[$r]}, have {$total}.",
                        'costs' => $costs,
                    ], 400);
                }
            }

            foreach (['wood', 'stone', 'food', 'metal'] as $r) {
                $remaining = $costs[$r];
                foreach ($playerCities as $city) {
                    if ($remaining <= 0) break;
                    $take = min($city->{$r}, $remaining);
                    $city->decrement($r, $take);
                    $remaining -= $take;
                }
            }
        }

        $city = $war->cities()->create([
            'owner_id' => $player->id,
            'name' => $cityCount > 0 ? 'Settlement ' . ($cityCount + 1) : 'Capital',
            'tile_x' => $tile->x,
            'tile_y' => $tile->y,
            'population' => $cityCount > 0 ? 50 : 100,
        ]);

        $tile->update(['owner_id' => $player->id, 'structure_id' => 'city']);

        return response()->json([
            'success' => true,
            'city' => [
                'id' => $city->id,
                'name' => $city->name,
                'tile_x' => $city->tile_x,
                'tile_y' => $city->tile_y,
                'population' => $city->population,
            ],
        ]);
    }
}
