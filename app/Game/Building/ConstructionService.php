<?php

namespace App\Game\Building;

use App\Models\City;
use App\Models\Base;
use App\Models\CityBuilding;
use App\Models\BaseBuilding;

class ConstructionService
{
    public function startCityConstruction(City $city, string $buildingType, float $constructionSpeed): CityBuilding
    {
        $building = CityBuilding::firstOrNew([
            'city_id' => $city->id,
            'type' => $buildingType,
        ]);

        $nextLevel = ($building->exists ? $building->level : 0) + 1;

        $cost = BuildingService::getCost($buildingType, $nextLevel - 1, $constructionSpeed);
        $buildTime = BuildingService::getBuildTime($buildingType, $nextLevel - 1, $constructionSpeed);

        $this->deductCityResources($city, $cost);

        $building->level = $nextLevel;
        $building->finishes_at = now()->addMinutes($buildTime);
        $building->save();

        return $building;
    }

    public function startBaseConstruction(Base $base, string $buildingType, float $constructionSpeed, City $fromCity): BaseBuilding
    {
        $building = BaseBuilding::firstOrNew([
            'base_id' => $base->id,
            'type' => $buildingType,
        ]);

        $nextLevel = ($building->exists ? $building->level : 0) + 1;

        $cost = BuildingService::getLevelCost($nextLevel, $constructionSpeed);
        $buildTime = BuildingService::getLevelBuildTime($nextLevel, $constructionSpeed);

        $this->deductCityResources($fromCity, $cost);

        $building->level = $nextLevel;
        $building->finishes_at = now()->addMinutes($buildTime);
        $building->save();

        return $building;
    }

    private function deductCityResources(City $city, array $cost): void
    {
        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            if (($cost[$r] ?? 0) > 0) {
                $city->decrement($r, $cost[$r]);
            }
        }
    }

    public function checkCityResources(City $city, array $cost): ?string
    {
        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            if (($cost[$r] ?? 0) > $city->{$r}) {
                return $r;
            }
        }
        return null;
    }

    public function completeCityConstructions(): int
    {
        $completed = CityBuilding::where('finishes_at', '<=', now())
            ->whereNotNull('finishes_at')
            ->get();

        $count = 0;
        foreach ($completed as $building) {
            $building->finishes_at = null;
            $building->save();
            $count++;
        }

        return $count;
    }

    public function completeBaseConstructions(): int
    {
        $completed = BaseBuilding::where('finishes_at', '<=', now())
            ->whereNotNull('finishes_at')
            ->get();

        $count = 0;
        foreach ($completed as $building) {
            $building->finishes_at = null;
            $building->save();
            $count++;
        }

        return $count;
    }
}
