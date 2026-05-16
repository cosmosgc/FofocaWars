<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Army;
use App\Models\City;
use App\Models\TrainingQueue;
use App\Models\Unit;
use App\Models\UnitType;
use App\Game\Army\ArmyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArmyController extends Controller
{
    public function movements(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $arrived = Army::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->where('status', 'marching')
            ->where('arrival_at', '<=', now())
            ->get();

        foreach ($arrived as $army) {
            app(\App\Game\Army\ArmyService::class)->resolveArrival($army, $war);
        }

        $armies = Army::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->whereIn('status', ['marching', 'returning'])
            ->with(['units.unitType', 'originCity', 'targetCity'])
            ->get()
            ->map(fn($army) => [
                'id' => $army->id,
                'status' => $army->status,
                'origin' => ['x' => $army->originCity->tile_x, 'y' => $army->originCity->tile_y],
                'target' => ['x' => $army->targetCity->tile_x, 'y' => $army->targetCity->tile_y],
                'arrival_at' => $army->arrival_at,
                'created_at' => $army->created_at,
            ]);
    }

    public function mapMovements(War $war)
    {
        $armies = Army::where('war_id', $war->id)
            ->where('status', 'marching')
            ->with(['originCity', 'targetCity', 'units.unitType'])
            ->get()
            ->map(fn($army) => [
                'id' => $army->id,
                'owner_id' => $army->owner_id,
                'origin' => ['x' => $army->originCity->tile_x, 'y' => $army->originCity->tile_y],
                'target' => ['x' => $army->targetCity->tile_x, 'y' => $army->targetCity->tile_y],
                'arrival_at' => $army->arrival_at,
                'created_at' => $army->created_at,
                'units' => $army->units->map(fn($au) => [
                    'name' => $au->unitType->name,
                    'quantity' => $au->quantity,
                ]),
            ]);

        return response()->json($armies);
    }

    public function unitTypes(War $war)
    {
        $types = UnitType::all()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'role' => $t->role,
            'attack' => $t->attack,
            'defense' => $t->defense,
            'speed' => $t->speed,
            'cost' => [
                'wood' => $t->wood_cost,
                'stone' => $t->stone_cost,
                'food' => $t->food_cost,
                'metal' => $t->metal_cost,
            ],
            'population_cost' => $t->population_cost,
        ]);

        return response()->json($types);
    }

    public function cityUnits(War $war, City $city, ArmyService $armyService)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($city->war_id !== $war->id || $city->owner_id !== $player->id, 403);

        $stationed = $armyService->getStationedArmies($city);
        $units = Unit::where('city_id', $city->id)
            ->where('quantity', '>', 0)
            ->with('unitType')
            ->get()
            ->map(fn($u) => [
                'unit_type_id' => $u->unit_type_id,
                'name' => $u->unitType->name,
                'quantity' => $u->quantity,
            ]);

        return response()->json([
            'units' => $units,
            'stationed_armies' => $stationed->map(fn($a) => [
                'id' => $a->id,
                'units' => $a->units->map(fn($au) => [
                    'name' => $au->unitType->name,
                    'quantity' => $au->quantity,
                ]),
            ]),
        ]);
    }

    public function trainingQueue(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cities = City::where('war_id', $war->id)->where('owner_id', $player->id)->pluck('id');

        $finished = TrainingQueue::whereIn('city_id', $cities)
            ->where('finishes_at', '<=', now())
            ->get();

        foreach ($finished as $entry) {
            $unit = Unit::firstOrCreate(
                ['city_id' => $entry->city_id, 'unit_type_id' => $entry->unit_type_id],
                ['quantity' => 0]
            );
            $unit->increment('quantity', $entry->quantity);
            $entry->delete();
        }

        $queue = TrainingQueue::whereIn('city_id', $cities)
            ->with('unitType')
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'city_id' => $q->city_id,
                'unit' => $q->unitType->name,
                'quantity' => $q->quantity,
                'finishes_at' => $q->finishes_at,
            ]);

        return response()->json($queue);
    }

    public function garrisons(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $garrisons = Army::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->where('status', 'stationed')
            ->where('mission', 'reinforce')
            ->with(['units.unitType', 'targetCity'])
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'target_name' => $a->targetCity->name,
                'units' => $a->units->map(fn($au) => [
                    'name' => $au->unitType->name,
                    'quantity' => $au->quantity,
                ]),
                'recall_url' => route('armies.recall', [$war, $a]),
            ]);

        return response()->json($garrisons);
    }

    public function train(War $war, Request $request)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'unit_type_id' => 'required|exists:unit_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $city = City::where('war_id', $war->id)
            ->where('owner_id', $player->id)
            ->findOrFail($validated['city_id']);

        $unitType = UnitType::findOrFail($validated['unit_type_id']);
        $quantity = (int) $validated['quantity'];

        $totalWood = $unitType->wood_cost * $quantity;
        $totalStone = $unitType->stone_cost * $quantity;
        $totalFood = $unitType->food_cost * $quantity;
        $totalMetal = $unitType->metal_cost * $quantity;

        if ($city->wood < $totalWood || $city->stone < $totalStone ||
            $city->food < $totalFood || $city->metal < $totalMetal) {
            return response()->json(['error' => 'Insufficient resources.'], 422);
        }

        $city->decrement('wood', $totalWood);
        $city->decrement('stone', $totalStone);
        $city->decrement('food', $totalFood);
        $city->decrement('metal', $totalMetal);

        $timePerUnit = $unitType->training_time * (float) $war->construction_speed;
        $finishesAt = now()->addMinutes(max(1, (int) ceil($timePerUnit * $quantity)));

        TrainingQueue::create([
            'city_id' => $city->id,
            'unit_type_id' => $unitType->id,
            'quantity' => $quantity,
            'finishes_at' => $finishesAt,
        ]);

        return response()->json([
            'message' => "Training {$quantity} {$unitType->name}(s). Ready " . $finishesAt->diffForHumans(),
            'finishes_at' => $finishesAt,
        ]);
    }
}
