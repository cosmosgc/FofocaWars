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
            ->with('owner')
            ->get(['id', 'owner_id', 'name', 'tile_x', 'tile_y', 'population']);

        return response()->json($cities);
    }
}
