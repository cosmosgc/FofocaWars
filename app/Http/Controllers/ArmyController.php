<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Models\Army;
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
            'mission' => 'required|in:attack,reinforce',
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

        $armyService->sendArmy($origin, $target, $units, $war, $validated['mission']);

        return redirect()->route('armies.index', $war)
            ->with('success', 'Army dispatched.');
    }

    public function recall(War $war, \App\Models\Army $army, Request $request, ArmyService $armyService)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($army->war_id !== $war->id || $army->owner_id !== $player->id, 403);

        $armyService->recallGarrison($army);

        return redirect()->route('armies.index', $war)
            ->with('success', 'Garrison recalled.');
    }
}
