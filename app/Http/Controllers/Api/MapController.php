<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\Tile;
use Illuminate\Http\Request;

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
}
