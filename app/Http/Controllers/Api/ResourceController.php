<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Game\Economy\ResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    public function index(War $war, ResourceService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cities = City::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->get()
            ->each(fn($city) => $service->syncCity($city, $war))
            ->map(fn($city) => array_merge(
                $city->only(['id', 'name', 'wood', 'stone', 'food', 'metal', 'population', 'max_wood', 'max_stone', 'max_food', 'max_metal']),
                ['url' => route('cities.show', [$war, $city])]
            ));

        $rates = $service->getProductionRates($war);

        return response()->json([
            'cities' => $cities,
            'rates' => $rates,
        ]);
    }
}
