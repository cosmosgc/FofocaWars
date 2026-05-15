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

    public function syncCity(City $city, War $war): void
    {
        $multiplier = (float) $war->resource_multiplier;
        $elapsedMinutes = $city->updated_at->diffInMinutes(now(), false);

        if ($elapsedMinutes <= 0) {
            return;
        }

        $city->wood = min($city->max_wood, $city->wood + (int) round($this->baseProduction['wood'] * $multiplier * $elapsedMinutes));
        $city->stone = min($city->max_stone, $city->stone + (int) round($this->baseProduction['stone'] * $multiplier * $elapsedMinutes));
        $city->food = min($city->max_food, $city->food + (int) round($this->baseProduction['food'] * $multiplier * $elapsedMinutes));
        $city->metal = min($city->max_metal, $city->metal + (int) round($this->baseProduction['metal'] * $multiplier * $elapsedMinutes));

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
        ];
    }
}
