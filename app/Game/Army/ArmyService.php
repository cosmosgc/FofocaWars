<?php

namespace App\Game\Army;

use App\Models\War;
use App\Models\Army;
use App\Models\ArmyUnit;
use App\Models\City;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Support\Facades\DB;

class ArmyService
{
    public function sendArmy(City $origin, City $target, array $units, War $war, string $mission = 'attack'): Army
    {
        return DB::transaction(function () use ($origin, $target, $units, $war, $mission) {
            $distance = $this->calculateDistance($origin, $target);
            $speed = $this->calculateSpeed($units, $war);
            $travelMinutes = $distance / max($speed, 1);

            $army = Army::create([
                'war_id' => $war->id,
                'owner_id' => $origin->owner_id,
                'origin_city_id' => $origin->id,
                'target_city_id' => $target->id,
                'status' => 'marching',
                'mission' => $mission,
                'arrival_at' => now()->addMinutes(max(1, (int) ceil($travelMinutes))),
            ]);

            foreach ($units as $unitTypeId => $quantity) {
                if ($quantity <= 0) continue;

                $unitType = UnitType::findOrFail($unitTypeId);
                $unit = Unit::where('city_id', $origin->id)
                    ->where('unit_type_id', $unitTypeId)
                    ->firstOrFail();

                $deduct = min($quantity, $unit->quantity);
                $unit->decrement('quantity', $deduct);

                ArmyUnit::create([
                    'army_id' => $army->id,
                    'unit_type_id' => $unitTypeId,
                    'quantity' => $deduct,
                ]);
            }

            return $army->fresh()->load('units');
        });
    }

    public function tickArrivals(War $war): void
    {
        $armies = Army::where('war_id', $war->id)
            ->where('status', 'marching')
            ->where('arrival_at', '<=', now())
            ->get();

        foreach ($armies as $army) {
            $this->resolveArrival($army, $war);
        }
    }

    public function resolveArrival(Army $army, War $war): void
    {
        if ($army->mission === 'reinforce') {
            $this->reinforce($army);
            return;
        }

        $targetCity = $army->targetCity;
        $defenderUnits = Unit::where('city_id', $targetCity->id)
            ->where('quantity', '>', 0)
            ->get()
            ->keyBy('unit_type_id');

        $hasDefenders = $defenderUnits->isNotEmpty();

        if ($hasDefenders) {
            app(\App\Game\Battle\BattleService::class)->resolve($army, $targetCity, $war);
        } else {
            $this->occupy($army, $targetCity);
        }
    }

    public function reinforce(Army $army): void
    {
        DB::transaction(function () use ($army) {
            $army->update(['status' => 'stationed']);
        });
    }

    public function recallGarrison(Army $army): void
    {
        if ($army->mission !== 'reinforce' || $army->status !== 'stationed') return;

        $distance = $this->calculateDistance($army->originCity, $army->targetCity);
        $speed = $this->calculateSpeedForArmy($army);
        $travelMinutes = $distance / max($speed, 1);

        $army->update([
            'status' => 'returning',
            'arrival_at' => now()->addMinutes(max(1, (int) ceil($travelMinutes))),
        ]);
    }

    public function occupy(Army $army, City $city): void
    {
        DB::transaction(function () use ($army, $city) {
            $army->update(['status' => 'stationed']);
            $city->update(['owner_id' => $army->owner_id]);
        });
    }

    public function recallArmy(Army $army): void
    {
        if ($army->status !== 'marching') return;

        DB::transaction(function () use ($army) {
            $originCity = $army->originCity;

            foreach ($army->units as $au) {
                $unit = Unit::firstOrCreate(
                    ['city_id' => $originCity->id, 'unit_type_id' => $au->unit_type_id],
                    ['quantity' => 0]
                );
                $unit->increment('quantity', $au->quantity);
            }

            $army->update(['status' => 'recalled']);
            $army->units()->delete();
        });
    }

    public function getStationedArmies(City $city)
    {
        return Army::where('origin_city_id', $city->id)
            ->where('status', 'stationed')
            ->with('units.unitType')
            ->get();
    }

    public function calculateSpeedForArmy(Army $army): float
    {
        $totalSpeed = 0;
        $totalQty = 0;
        foreach ($army->units as $au) {
            $totalSpeed += $au->unitType->speed * $au->quantity;
            $totalQty += $au->quantity;
        }
        return $totalQty > 0 ? $totalSpeed / $totalQty : 1;
    }

    public function calculateDistance(City $a, City $b): float
    {
        $dx = $a->tile_x - $b->tile_x;
        $dy = $a->tile_y - $b->tile_y;
        return sqrt($dx * $dx + $dy * $dy);
    }

    public function calculateSpeed(array $units, War $war): float
    {
        if (empty($units)) return 1;

        $totalSpeed = 0;
        $totalQty = 0;

        foreach ($units as $unitTypeId => $quantity) {
            if ($quantity <= 0) continue;
            $unitType = UnitType::find($unitTypeId);
            if ($unitType) {
                $totalSpeed += $unitType->speed * $quantity;
                $totalQty += $quantity;
            }
        }

        $avgSpeed = $totalQty > 0 ? $totalSpeed / $totalQty : 1;
        return $avgSpeed * (float) $war->troop_speed_multiplier;
    }
}
