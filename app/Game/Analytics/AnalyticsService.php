<?php

namespace App\Game\Analytics;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\City;
use App\Models\Base;
use App\Models\Tile;
use App\Models\Unit;
use App\Models\ArmyUnit;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function snapshot(War $war): void
    {
        $players = WarPlayer::where('war_id', $war->id)->get();

        foreach ($players as $player) {
            $cities = City::where('war_id', $war->id)
                ->where('owner_id', $player->id)
                ->get();

            $totalWood = $cities->sum('wood');
            $totalStone = $cities->sum('stone');
            $totalFood = $cities->sum('food');
            $totalMetal = $cities->sum('metal');

            DB::table('resource_history')->insert([
                'war_id' => $war->id,
                'war_player_id' => $player->id,
                'wood' => $totalWood,
                'stone' => $totalStone,
                'food' => $totalFood,
                'metal' => $totalMetal,
                'total' => $totalWood + $totalStone + $totalFood + $totalMetal,
                'recorded_at' => now(),
            ]);

            $cityIds = $cities->pluck('id');

            $cityUnits = Unit::whereIn('city_id', $cityIds)->with('unitType')->get();
            $garrisonUnits = ArmyUnit::whereHas('army', fn($q) => $q
                ->where('war_id', $war->id)
                ->where('owner_id', $player->id)
                ->where('status', 'stationed')
            )->with('unitType')->get();

            $totalUnits = $cityUnits->sum('quantity') + $garrisonUnits->sum('quantity');
            $attackPower = 0;
            $defensePower = 0;

            foreach ($cityUnits as $u) {
                $attackPower += ($u->unitType->attack ?? 0) * $u->quantity;
                $defensePower += ($u->unitType->defense ?? 0) * $u->quantity;
            }
            foreach ($garrisonUnits as $u) {
                $attackPower += ($u->unitType->attack ?? 0) * $u->quantity;
                $defensePower += ($u->unitType->defense ?? 0) * $u->quantity;
            }

            DB::table('army_history')->insert([
                'war_id' => $war->id,
                'war_player_id' => $player->id,
                'total_units' => $totalUnits,
                'attack_power' => $attackPower,
                'defense_power' => $defensePower,
                'recorded_at' => now(),
            ]);

            $cityCount = $cities->count();
            $baseCount = Base::where('war_id', $war->id)
                ->where('owner_id', $player->id)
                ->count();
            $tileCount = Tile::where('war_id', $war->id)
                ->where('owner_id', $player->id)
                ->count();

            DB::table('territory_history')->insert([
                'war_id' => $war->id,
                'war_player_id' => $player->id,
                'city_count' => $cityCount,
                'base_count' => $baseCount,
                'tile_count' => $tileCount,
                'recorded_at' => now(),
            ]);
        }
    }

    public function ranking(War $war): array
    {
        $players = WarPlayer::where('war_id', $war->id)->with('user')->get();
        $rows = [];

        foreach ($players as $p) {
            $cities = City::where('war_id', $war->id)
                ->where('owner_id', $p->id)
                ->get();
            $cityCount = $cities->count();
            $totalResources = $cities->sum('wood') + $cities->sum('stone')
                + $cities->sum('food') + $cities->sum('metal');

            $cityUnitQty = Unit::whereIn('city_id', $cities->pluck('id'))->sum('quantity');

            $garrisonQty = ArmyUnit::whereHas('army', fn($q) => $q
                ->where('war_id', $war->id)
                ->where('owner_id', $p->id)
                ->where('status', 'stationed')
            )->sum('quantity');

            $baseCount = Base::where('war_id', $war->id)
                ->where('owner_id', $p->id)
                ->count();

            $tileCount = Tile::where('war_id', $war->id)
                ->where('owner_id', $p->id)
                ->count();

            $rows[] = [
                'name' => $p->user?->name ?? 'Unknown',
                'cities' => $cityCount,
                'bases' => $baseCount,
                'tiles' => $tileCount,
                'total_resources' => $totalResources,
                'total_units' => $cityUnitQty + $garrisonQty,
            ];
        }

        return $rows;
    }
}
