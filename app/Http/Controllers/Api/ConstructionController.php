<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\City;
use App\Models\Base;
use App\Models\WarPlayer;
use App\Game\Building\BuildingService;
use App\Game\Building\ConstructionService;
use App\Game\Economy\ResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConstructionController extends Controller
{
    public function cityBuildings(War $war, City $city)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($city->war_id !== $war->id || $city->owner_id !== $player->id, 403);

        $defs = BuildingService::cityBuildings();
        $existing = $city->buildings()->get()->keyBy('type');

        $buildings = [];
        foreach ($defs as $type => $def) {
            $b = $existing->get($type);
            $level = $b ? $b->level : 0;
            $finishesAt = $b?->finishes_at;

            $nextLevel = $level + 1;
            $maxed = $nextLevel > $def['max_level'];

            $buildings[] = [
                'type' => $type,
                'name' => $def['name'],
                'icon' => $def['icon'],
                'description' => $def['description'],
                'level' => $level,
                'max_level' => $def['max_level'],
                'finishes_at' => $finishesAt,
                'is_under_construction' => $finishesAt?->isFuture() ?? false,
                'can_upgrade' => !$maxed && (!$finishesAt || !$finishesAt->isFuture()),
                'next_costs' => !$maxed ? BuildingService::getCost($type, $level, (float) $war->construction_speed) : null,
                'build_time' => !$maxed ? BuildingService::getBuildTime($type, $level, (float) $war->construction_speed) : null,
                'effects' => $def['effects'],
                'pos_x' => $b?->pos_x,
                'pos_y' => $b?->pos_y,
            ];
        }

        return response()->json(['buildings' => $buildings]);
    }

    public function constructCity(War $war, City $city, Request $request, ConstructionService $service, ResourceService $resourceService)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($city->war_id !== $war->id || $city->owner_id !== $player->id, 403);

        if ($war->status === 'ended') {
            return response()->json(['error' => 'War has ended.'], 400);
        }

        $validated = $request->validate([
            'type' => 'required|string',
        ]);

        $type = $validated['type'];
        $defs = BuildingService::cityBuildings();

        if (!isset($defs[$type])) {
            return response()->json(['error' => 'Invalid building type.'], 400);
        }

        $existing = $city->buildings()->where('type', $type)->first();
        $currentLevel = $existing ? $existing->level : 0;

        if ($currentLevel >= $defs[$type]['max_level']) {
            return response()->json(['error' => 'Building already at max level.'], 400);
        }

        if ($existing && $existing->finishes_at && $existing->finishes_at->isFuture()) {
            return response()->json(['error' => 'Building is already under construction.'], 400);
        }

        $cost = BuildingService::getCost($type, $currentLevel, (float) $war->construction_speed);

        $missing = $service->checkCityResources($city, $cost);
        if ($missing) {
            return response()->json([
                'error' => "Not enough {$missing}. Need {$cost[$missing]}, have {$city->{$missing}}.",
                'costs' => $cost,
            ], 400);
        }

        $service->startCityConstruction($city, $type, (float) $war->construction_speed);

        $resourceService->syncCity($city, $war);

        return response()->json([
            'success' => true,
            'message' => __('Construction started!'),
            'city' => [
                'id' => $city->id,
                'wood' => $city->fresh()->wood,
                'stone' => $city->fresh()->stone,
                'food' => $city->fresh()->food,
                'metal' => $city->fresh()->metal,
            ],
        ]);
    }

    public function baseBuildings(War $war, Base $base)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($base->war_id !== $war->id || $base->owner_id !== $player->id, 403);

        $defs = BuildingService::getBaseBuildingsForType($base->type);
        $existing = $base->buildings()->get()->keyBy('type');

        $buildings = [];
        foreach ($defs as $type => $def) {
            $b = $existing->get($type);
            $level = $b ? $b->level : 0;
            $finishesAt = $b?->finishes_at;
            $nextLevel = $level + 1;

            $buildings[] = [
                'type' => $type,
                'name' => $def['name'],
                'icon' => $def['icon'],
                'description' => $def['description'],
                'level' => $level,
                'max_level' => 5,
                'finishes_at' => $finishesAt,
                'is_under_construction' => $finishesAt?->isFuture() ?? false,
                'can_upgrade' => $nextLevel <= 5 && (!$finishesAt || !$finishesAt->isFuture()),
                'next_costs' => $nextLevel <= 5 ? BuildingService::getLevelCost($nextLevel, (float) $war->construction_speed) : null,
                'build_time' => $nextLevel <= 5 ? BuildingService::getLevelBuildTime($nextLevel, (float) $war->construction_speed) : null,
                'effects' => $def['effects'],
                'pos_x' => $b?->pos_x,
                'pos_y' => $b?->pos_y,
            ];
        }

        return response()->json(['buildings' => $buildings]);
    }

    public function constructBase(War $war, Base $base, Request $request, ConstructionService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($base->war_id !== $war->id || $base->owner_id !== $player->id, 403);

        if ($war->status === 'ended') {
            return response()->json(['error' => 'War has ended.'], 400);
        }

        $validated = $request->validate([
            'type' => 'required|string',
        ]);

        $type = $validated['type'];
        $defs = BuildingService::getBaseBuildingsForType($base->type);

        if (!isset($defs[$type])) {
            return response()->json(['error' => 'Invalid building type for this base.'], 400);
        }

        $existing = $base->buildings()->where('type', $type)->first();
        $currentLevel = $existing ? $existing->level : 0;

        if ($currentLevel >= 5) {
            return response()->json(['error' => 'Building already at max level.'], 400);
        }

        if ($existing && $existing->finishes_at && $existing->finishes_at->isFuture()) {
            return response()->json(['error' => 'Building is already under construction.'], 400);
        }

        $cost = BuildingService::getLevelCost($currentLevel + 1, (float) $war->construction_speed);

        $playerCities = City::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->get();

        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            $total = $playerCities->sum($r);
            if ($total < $cost[$r]) {
                return response()->json([
                    'error' => "Not enough {$r}. Need {$cost[$r]}, have {$total}.",
                    'costs' => $cost,
                ], 400);
            }
        }

        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            $remaining = $cost[$r];
            foreach ($playerCities as $city) {
                if ($remaining <= 0) break;
                $take = min($city->{$r}, $remaining);
                $city->decrement($r, $take);
                $remaining -= $take;
            }
        }

        $service->startBaseConstruction($base, $type, (float) $war->construction_speed, $playerCities->first());

        return response()->json([
            'success' => true,
            'message' => __('Construction started!'),
        ]);
    }

    public function saveCityPositions(War $war, City $city, Request $request)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($city->war_id !== $war->id || $city->owner_id !== $player->id, 403);

        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.type' => 'required|string',
            'positions.*.pos_x' => 'nullable|integer',
            'positions.*.pos_y' => 'nullable|integer',
        ]);

        foreach ($validated['positions'] as $pos) {
            $city->buildings()->where('type', $pos['type'])->update([
                'pos_x' => $pos['pos_x'],
                'pos_y' => $pos['pos_y'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function saveBasePositions(War $war, Base $base, Request $request)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($base->war_id !== $war->id || $base->owner_id !== $player->id, 403);

        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.type' => 'required|string',
            'positions.*.pos_x' => 'nullable|integer',
            'positions.*.pos_y' => 'nullable|integer',
        ]);

        foreach ($validated['positions'] as $pos) {
            $base->buildings()->where('type', $pos['type'])->update([
                'pos_x' => $pos['pos_x'],
                'pos_y' => $pos['pos_y'],
            ]);
        }

        return response()->json(['success' => true]);
    }
}
