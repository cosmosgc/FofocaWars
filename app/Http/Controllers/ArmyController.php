<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Game\Army\ArmyService;
use App\Game\Economy\ResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArmyController extends Controller
{
    public function index(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cities = $war->cities()->where('owner_id', $player->id)->get();
        $unitTypes = \App\Models\UnitType::all();

        return view('armies.index', compact('war', 'player', 'cities', 'unitTypes'));
    }

    public function send(War $war, Request $request, ArmyService $armyService, ResourceService $resourceService)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'target_city_id' => 'required|exists:cities,id|different:origin_city_id',
            'units' => 'required|array|min:1',
            'units.*' => 'integer|min:0',
        ]);

        $origin = City::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->findOrFail($validated['origin_city_id']);

        $target = City::where('war_id', $war->id)
            ->findOrFail($validated['target_city_id']);

        $units = array_filter($validated['units'], fn($qty) => $qty > 0);

        if (empty($units)) {
            return back()->with('error', 'Select at least one unit.');
        }

        $armyService->sendArmy($origin, $target, $units, $war);

        return redirect()->route('armies.index', $war)
            ->with('success', 'Army dispatched.');
    }
}
