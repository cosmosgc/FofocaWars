<?php

namespace App\Game\Economy;

use App\Models\City;
use App\Models\War;
use App\Game\Building\BuildingService;

class ResourceService
{
    private array $baseProduction = [
        'wood' => 10,
        'stone' => 8,
        'food' => 15,
        'metal' => 5,
    ];

    private function getCityProductionBonuses(City $city): array
    {
        $effects = BuildingService::getCityBuildingEffects($city);
        $globalEffects = BuildingService::getAllPlayerBuildingEffects($city->war_id, $city->owner_id);

        $productionMult = 1 + (($effects['production_mult'] ?? 0) + ($globalEffects['production_mult'] ?? 0)) / 100;
        $maxResourceMult = 1 + (($effects['max_resource_mult'] ?? 0) + ($globalEffects['max_resource_mult'] ?? 0)) / 100;

        return [
            'wood_add' => $effects['wood_production'] ?? 0,
            'stone_add' => $effects['stone_production'] ?? 0,
            'food_add' => $effects['food_production'] ?? 0,
            'metal_add' => $effects['metal_production'] ?? 0,
            'production_mult' => $productionMult,
            'max_resource_mult' => $maxResourceMult,
        ];
    }

    public function tickWar(War $war): void
    {
        $cities = City::where('war_id', $war->id)->get();

        foreach ($cities as $city) {
            $this->tickCity($city, $war);
        }
    }

    public function tickCity(City $city, War $war): void
    {
        $multiplier = (float) $war->resource_multiplier;

        self::applyProduction($city, $multiplier, 1);
    }

    public function syncCity(City $city, War $war): void
    {
        $multiplier = (float) $war->resource_multiplier;
        $elapsedMinutes = $city->updated_at->diffInMinutes(now(), false);

        if ($elapsedMinutes <= 0) {
            return;
        }

        self::applyProduction($city, $multiplier, $elapsedMinutes);
    }

    private function applyProduction(City $city, float $multiplier, float $minutes): void
    {
        $bonuses = $this->getCityProductionBonuses($city);

        $woodProd = ($this->baseProduction['wood'] + $bonuses['wood_add']) * $multiplier * $bonuses['production_mult'] * $minutes;
        $stoneProd = ($this->baseProduction['stone'] + $bonuses['stone_add']) * $multiplier * $bonuses['production_mult'] * $minutes;
        $foodProd = ($this->baseProduction['food'] + $bonuses['food_add']) * $multiplier * $bonuses['production_mult'] * $minutes;
        $metalProd = ($this->baseProduction['metal'] + $bonuses['metal_add']) * $multiplier * $bonuses['production_mult'] * $minutes;

        $maxWood = (int) round($city->max_wood * $bonuses['max_resource_mult']);
        $maxStone = (int) round($city->max_stone * $bonuses['max_resource_mult']);
        $maxFood = (int) round($city->max_food * $bonuses['max_resource_mult']);
        $maxMetal = (int) round($city->max_metal * $bonuses['max_resource_mult']);

        $city->wood = min($maxWood, $city->wood + (int) round($woodProd));
        $city->stone = min($maxStone, $city->stone + (int) round($stoneProd));
        $city->food = min($maxFood, $city->food + (int) round($foodProd));
        $city->metal = min($maxMetal, $city->metal + (int) round($metalProd));

        $city->save();
    }

    public function getProductionRates(War $war): array
    {
        $multiplier = (float) $war->resource_multiplier;

        return [
            'wood' => (int) round($this->baseProduction['wood'] * $multiplier),
            'stone' => (int) round($this->baseProduction['stone'] * $multiplier),
            'food' => (int) round($this->baseProduction['food'] * $multiplier),
            'metal' => (int) round($this->baseProduction['metal'] * $multiplier),
            'wood_per_min' => (int) round($this->baseProduction['wood'] * $multiplier),
            'stone_per_min' => (int) round($this->baseProduction['stone'] * $multiplier),
            'food_per_min' => (int) round($this->baseProduction['food'] * $multiplier),
            'metal_per_min' => (int) round($this->baseProduction['metal'] * $multiplier),
        ];
    }

    public function getEffectiveProductionRates(City $city, War $war): array
    {
        $multiplier = (float) $war->resource_multiplier;
        $bonuses = $this->getCityProductionBonuses($city);

        return [
            'wood' => (int) round(($this->baseProduction['wood'] + $bonuses['wood_add']) * $multiplier * $bonuses['production_mult']),
            'stone' => (int) round(($this->baseProduction['stone'] + $bonuses['stone_add']) * $multiplier * $bonuses['production_mult']),
            'food' => (int) round(($this->baseProduction['food'] + $bonuses['food_add']) * $multiplier * $bonuses['production_mult']),
            'metal' => (int) round(($this->baseProduction['metal'] + $bonuses['metal_add']) * $multiplier * $bonuses['production_mult']),
        ];
    }
}
