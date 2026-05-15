<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\City;
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

        $rates = $service->getProductionRates($war);

        return view('cities.show', compact('war', 'city', 'rates', 'player'));
    }
}
