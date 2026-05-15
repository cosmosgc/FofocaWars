<?php

namespace App\Game\Economy;

use App\Models\City;
use App\Models\War;

class ResourceService
{
    private array $baseProduction = [
        'wood' => 10,
        'stone' => 8,
        'food' => 15,
        'metal' => 5,
    ];

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

        $city->wood = min($city->max_wood, $city->wood + (int) round($this->baseProduction['wood'] * $multiplier));
        $city->stone = min($city->max_stone, $city->stone + (int) round($this->baseProduction['stone'] * $multiplier));
        $city->food = min($city->max_food, $city->food + (int) round($this->baseProduction['food'] * $multiplier));
        $city->metal = min($city->max_metal, $city->metal + (int) round($this->baseProduction['metal'] * $multiplier));

        $city->save();
    }

    public function getFillPercentages(City $city): array
    {
        return [
            'wood' => $city->max_wood > 0 ? round(($city->wood / $city->max_wood) * 100) : 0,
            'stone' => $city->max_stone > 0 ? round(($city->stone / $city->max_stone) * 100) : 0,
            'food' => $city->max_food > 0 ? round(($city->food / $city->max_food) * 100) : 0,
            'metal' => $city->max_metal > 0 ? round(($city->metal / $city->max_metal) * 100) : 0,
        ];
    }

    public function getProductionRates(War $war): array
    {
        $multiplier = (float) $war->resource_multiplier;

        return [
            'wood' => (int) round($this->baseProduction['wood'] * $multiplier),
            'stone' => (int) round($this->baseProduction['stone'] * $multiplier),
            'food' => (int) round($this->baseProduction['food'] * $multiplier),
            'metal' => (int) round($this->baseProduction['metal'] * $multiplier),
        ];
    }
}
