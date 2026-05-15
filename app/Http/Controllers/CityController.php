<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\City;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\WarPlayer;
use App\Game\Economy\ResourceService;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    public function show(War $war, City $city, ResourceService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($city->war_id !== $war->id || $city->owner_id !== $player->id, 403);

        $service->syncCity($city, $war);
        $rates = $service->getProductionRates($war);

        $unitTypes = UnitType::all();
        $cityUnits = Unit::where('city_id', $city->id)
            ->where('quantity', '>', 0)
            ->with('unitType')
            ->get();

        return view('cities.show', compact('war', 'city', 'rates', 'player', 'unitTypes', 'cityUnits'));
    }
}
