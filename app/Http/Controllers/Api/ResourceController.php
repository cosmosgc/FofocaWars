<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Game\Economy\ResourceService;
use App\Game\Building\BuildingService;
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
            ->map(function ($city) use ($war) {
                $cityEffects = BuildingService::getCityBuildingEffects($city);
                $globalEffects = BuildingService::getAllPlayerBuildingEffects($war->id, $city->owner_id);
                $maxMult = 1 + (($cityEffects['max_resource_mult'] ?? 0) + ($globalEffects['max_resource_mult'] ?? 0)) / 100;

                return array_merge(
                    $city->only(['id', 'name', 'wood', 'stone', 'food', 'metal', 'population']),
                    [
                        'max_wood' => (int) round($city->max_wood * $maxMult),
                        'max_stone' => (int) round($city->max_stone * $maxMult),
                        'max_food' => (int) round($city->max_food * $maxMult),
                        'max_metal' => (int) round($city->max_metal * $maxMult),
                        'url' => route('cities.show', [$war, $city]),
                    ]
                );
            });

        $rates = $service->getProductionRates($war);

        return response()->json([
            'cities' => $cities,
            'rates' => $rates,
        ]);
    }
}
