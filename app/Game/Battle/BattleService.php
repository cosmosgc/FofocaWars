<?php

namespace App\Game\Battle;

use App\Models\War;
use App\Models\Army;
use App\Models\City;
use App\Models\Unit;
use App\Models\BattleReport;
use App\Models\UnitType;
use Illuminate\Support\Facades\DB;

class BattleService
{
    public function resolve(Army $attackerArmy, City $defenderCity, War $war): BattleReport
    {
        return DB::transaction(function () use ($attackerArmy, $defenderCity, $war) {
            $attackPower = $this->calculateAttackPower($attackerArmy);
            $defensePower = $this->calculateDefensePower($defenderCity);

            $totalPower = $attackPower + $defensePower;
            $attackerRoll = $totalPower > 0 ? $attackPower / $totalPower : 0.5;
            $defenderRoll = $totalPower > 0 ? $defensePower / $totalPower : 0.5;

            $attackerWins = $attackerRoll > $defenderRoll;
            $winner = $attackerWins ? 'attacker' : 'defender';

            $attackerLosses = $this->calculateCasualties($attackerArmy, $defenderRoll, $attackerWins);
            $defenderLosses = $this->calculateDefenderCasualties($defenderCity, $attackerRoll, !$attackerWins);

            $this->applyAttackerLosses($attackerArmy, $attackerLosses);
            $this->applyDefenderLosses($defenderCity, $defenderLosses);

            if ($attackerWins) {
                $attackerArmy->update(['status' => 'stationed', 'target_city_id' => $defenderCity->id]);
                $this->transferResources($attackerArmy, $defenderCity);
                $defenderCity->update(['owner_id' => $attackerArmy->owner_id]);
            } else {
                $this->returnSurvivors($attackerArmy);
            }

            $report = BattleReport::create([
                'war_id' => $war->id,
                'attacker_army_id' => $attackerArmy->id,
                'defender_city_id' => $defenderCity->id,
                'winner' => $winner,
                'attacker_losses' => $attackerLosses,
                'defender_losses' => $defenderLosses,
                'details' => [
                    'attack_power' => round($attackPower, 1),
                    'defense_power' => round($defensePower, 1),
                    'attacker_roll' => round($attackerRoll, 3),
                    'defender_roll' => round($defenderRoll, 3),
                    'loot' => $attackerWins ? $this->calculateLoot($attackerArmy, $defenderCity) : null,
                ],
            ]);

            return $report;
        });
    }

    private function calculateAttackPower(Army $army): float
    {
        $power = 0;
        foreach ($army->units as $au) {
            $power += $au->unitType->attack * $au->quantity;
        }
        return $power;
    }

    private function calculateDefensePower(City $city): float
    {
        $power = 0;
        $units = Unit::where('city_id', $city->id)
            ->where('quantity', '>', 0)
            ->with('unitType')
            ->get();

        foreach ($units as $unit) {
            $power += ($unit->unitType->defense + $unit->unitType->attack * 0.5) * $unit->quantity;
        }

        $garrisons = \App\Models\Army::where('target_city_id', $city->id)
            ->where('status', 'stationed')
            ->where('mission', 'reinforce')
            ->with('units.unitType')
            ->get();

        foreach ($garrisons as $g) {
            foreach ($g->units as $gu) {
                $power += ($gu->unitType->defense + $gu->unitType->attack * 0.5) * $gu->quantity;
            }
        }

        return max($power, 5);
    }

    private function calculateCasualties(Army $army, float $enemyRoll, bool $won): array
    {
        $losses = [];
        $baseLossRate = $won ? 0.15 : 0.6;

        foreach ($army->units as $au) {
            $lossRate = $baseLossRate * $enemyRoll * 2;
            $lossRate = min(max($lossRate, 0.05), 0.9);
            $lost = max(1, (int) round($au->quantity * $lossRate));
            $losses[$au->unit_type_id] = min($lost, $au->quantity);
        }

        return $losses;
    }

    private function calculateDefenderCasualties(City $city, float $enemyRoll, bool $won): array
    {
        $losses = [];
        $baseLossRate = $won ? 0.6 : 0.15;
        $units = Unit::where('city_id', $city->id)->where('quantity', '>', 0)->get();

        foreach ($units as $unit) {
            $lossRate = $baseLossRate * $enemyRoll * 2;
            $lossRate = min(max($lossRate, 0.05), 0.9);
            $lost = max(1, (int) round($unit->quantity * $lossRate));
            $losses[$unit->unit_type_id] = min($lost, $unit->quantity);
        }

        return $losses;
    }

    private function applyAttackerLosses(Army $army, array $losses): void
    {
        foreach ($losses as $unitTypeId => $lost) {
            $au = $army->units()->where('unit_type_id', $unitTypeId)->first();
            if ($au) {
                $au->decrement('quantity', $lost);
                if ($au->quantity <= 0) $au->delete();
            }
        }
    }

    private function applyDefenderLosses(City $city, array $losses): void
    {
        foreach ($losses as $unitTypeId => $lost) {
            $unit = Unit::where('city_id', $city->id)
                ->where('unit_type_id', $unitTypeId)
                ->first();
            if ($unit) {
                $unit->decrement('quantity', $lost);
                if ($unit->quantity <= 0) $unit->delete();
            }
        }
    }

    private function returnSurvivors(Army $army): void
    {
        foreach ($army->units as $au) {
            if ($au->quantity <= 0) continue;
            $unit = Unit::firstOrCreate(
                ['city_id' => $army->origin_city_id, 'unit_type_id' => $au->unit_type_id],
                ['quantity' => 0]
            );
            $unit->increment('quantity', $au->quantity);
        }

        $army->update(['status' => 'returned']);
        $army->units()->delete();
    }

    private function calculateLoot(Army $attackerArmy, City $defenderCity): array
    {
        $origin = City::find($attackerArmy->origin_city_id);
        if (!$origin) return [];

        $resources = ['wood', 'stone', 'food', 'metal'];
        $loot = [];

        foreach ($resources as $r) {
            $half = (int) floor($defenderCity->{$r} / 2);
            $cap = $origin->{'max_' . $r} - $origin->{$r};
            $loot[$r] = max(0, min($half, $cap));
        }

        return $loot;
    }

    private function transferResources(Army $attackerArmy, City $defenderCity): void
    {
        $origin = City::find($attackerArmy->origin_city_id);
        if (!$origin) return;

        $loot = $this->calculateLoot($attackerArmy, $defenderCity);

        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            $amount = $loot[$r] ?? 0;
            if ($amount <= 0) continue;
            $defenderCity->decrement($r, $amount);
            $origin->increment($r, $amount);
        }
    }
}
