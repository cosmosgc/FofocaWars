<?php

namespace App\Game\Building;

use App\Models\City;
use App\Models\Base;

class BuildingService
{
    public static function cityBuildings(): array
    {
        return [
            'lumber_mill' => [
                'name' => __('Lumber Mill'),
                'icon' => '🪵',
                'description' => __('Wood production +5 per level'),
                'effects' => ['wood_production' => 5],
                'costs' => ['wood' => 100, 'stone' => 50],
                'cost_multiplier' => 1.8,
                'build_time' => 2,
                'max_level' => 10,
            ],
            'quarry' => [
                'name' => __('Quarry'),
                'icon' => '🪨',
                'description' => __('Stone production +4 per level'),
                'effects' => ['stone_production' => 4],
                'costs' => ['stone' => 80, 'wood' => 40],
                'cost_multiplier' => 1.8,
                'build_time' => 2,
                'max_level' => 10,
            ],
            'farm' => [
                'name' => __('Farm'),
                'icon' => '🌾',
                'description' => __('Food production +8 per level'),
                'effects' => ['food_production' => 8],
                'costs' => ['wood' => 60, 'food' => 30],
                'cost_multiplier' => 1.8,
                'build_time' => 2,
                'max_level' => 10,
            ],
            'smelter' => [
                'name' => __('Smelter'),
                'icon' => '⚙️',
                'description' => __('Metal production +3 per level'),
                'effects' => ['metal_production' => 3],
                'costs' => ['stone' => 70, 'metal' => 40],
                'cost_multiplier' => 1.8,
                'build_time' => 2,
                'max_level' => 10,
            ],
            'barracks' => [
                'name' => __('Barracks'),
                'icon' => '⚔️',
                'description' => __('Training speed +5% per level'),
                'effects' => ['training_speed' => 5],
                'costs' => ['wood' => 150, 'stone' => 100],
                'cost_multiplier' => 2.0,
                'build_time' => 3,
                'max_level' => 5,
            ],
            'wall' => [
                'name' => __('Wall'),
                'icon' => '🧱',
                'description' => __('Defense +100 per level'),
                'effects' => ['defense_bonus' => 100],
                'costs' => ['stone' => 100, 'wood' => 80],
                'cost_multiplier' => 2.0,
                'build_time' => 3,
                'max_level' => 5,
            ],
            'market' => [
                'name' => __('Market'),
                'icon' => '🏪',
                'description' => __('Max resources +10% per level'),
                'effects' => ['max_resource_mult' => 10],
                'costs' => ['wood' => 120, 'stone' => 120],
                'cost_multiplier' => 1.8,
                'build_time' => 3,
                'max_level' => 5,
            ],
            'town_hall' => [
                'name' => __('Town Hall'),
                'icon' => '🏛️',
                'description' => __('All production +5% per level'),
                'effects' => ['production_mult' => 5],
                'costs' => ['wood' => 200, 'stone' => 200, 'food' => 100, 'metal' => 100],
                'cost_multiplier' => 2.5,
                'build_time' => 5,
                'max_level' => 5,
            ],
        ];
    }

    public static function baseBuildings(): array
    {
        return [
            'resource' => [
                'warehouse' => [
                    'name' => __('Warehouse'),
                    'icon' => '📦',
                    'description' => __('Resource caps +10% per level'),
                    'effects' => ['max_resource_mult' => 10],
                ],
                'supply_depot' => [
                    'name' => __('Supply Depot'),
                    'icon' => '🏭',
                    'description' => __('All production +5% per level'),
                    'effects' => ['production_mult' => 5],
                ],
            ],
            'military' => [
                'drill_ground' => [
                    'name' => __('Drill Ground'),
                    'icon' => '🏟️',
                    'description' => __('Training speed +5% per level'),
                    'effects' => ['training_speed' => 5],
                ],
                'armory' => [
                    'name' => __('Armory'),
                    'icon' => '🛡️',
                    'description' => __('Garrison defense +10% per level'),
                    'effects' => ['defense_mult' => 10],
                ],
            ],
            'trade' => [
                'marketplace' => [
                    'name' => __('Marketplace'),
                    'icon' => '🛒',
                    'description' => __('Resource caps +8% per level'),
                    'effects' => ['max_resource_mult' => 8],
                ],
                'escort_office' => [
                    'name' => __('Escort Office'),
                    'icon' => '🐎',
                    'description' => __('Army speed +5% per level'),
                    'effects' => ['troop_speed' => 5],
                ],
            ],
            'alliance' => [
                'embassy' => [
                    'name' => __('Embassy'),
                    'icon' => '🏛️',
                    'description' => __('Alliance bonus +5% per level'),
                    'effects' => ['alliance_bonus' => 5],
                ],
                'beacon' => [
                    'name' => __('Beacon Tower'),
                    'icon' => '🗼',
                    'description' => __('Army speed +5% per level'),
                    'effects' => ['troop_speed' => 5],
                ],
            ],
        ];
    }

    public static function getBaseBuildingsForType(string $type): array
    {
        $all = self::baseBuildings();
        return $all[$type] ?? [];
    }

    public static function defaultCosts(): array
    {
        return ['wood' => 80, 'stone' => 80, 'food' => 40, 'metal' => 40];
    }

    public static function getCost(string $buildingType, int $level, float $constructionSpeed): array
    {
        $cityDefs = self::cityBuildings();
        $baseDef = $cityDefs[$buildingType] ?? null;

        if ($baseDef) {
            $costs = $baseDef['costs'];
            $mult = $baseDef['cost_multiplier'];
            $scale = max(1, $constructionSpeed);
            $result = [];
            foreach (['wood', 'stone', 'food', 'metal'] as $r) {
                $base = $costs[$r] ?? 0;
                $result[$r] = (int) round($base * pow($mult, $level) * $scale);
            }
            return $result;
        }

        // base building or fallback
        $scale = max(1, $constructionSpeed);
        $result = [];
        foreach (['wood', 'stone', 'food', 'metal'] as $r) {
            $result[$r] = (int) round(self::defaultCosts()[$r] * pow(1.8, $level) * $scale);
        }
        return $result;
    }

    public static function getBuildTime(string $buildingType, int $level, float $constructionSpeed): int
    {
        $cityDefs = self::cityBuildings();
        $baseDef = $cityDefs[$buildingType] ?? null;

        $baseTime = $baseDef ? $baseDef['build_time'] : 3;
        return max(1, (int) round($baseTime * pow(1.5, $level) * max(1, $constructionSpeed)));
    }

    public static function getLevelCost(int $level, float $constructionSpeed): array
    {
        $scale = max(1, $constructionSpeed);
        return [
            'wood' => (int) round(80 * pow(1.8, $level - 1) * $scale),
            'stone' => (int) round(80 * pow(1.8, $level - 1) * $scale),
            'food' => (int) round(40 * pow(1.8, $level - 1) * $scale),
            'metal' => (int) round(40 * pow(1.8, $level - 1) * $scale),
        ];
    }

    public static function getLevelBuildTime(int $level, float $constructionSpeed): int
    {
        return max(1, (int) round(3 * pow(1.5, $level - 1) * max(1, $constructionSpeed)));
    }

    public static function getCityBuildingEffects(City $city): array
    {
        $buildings = $city->buildings()->get()->keyBy('type');

        $effects = [
            'wood_production' => 0,
            'stone_production' => 0,
            'food_production' => 0,
            'metal_production' => 0,
            'training_speed' => 0,
            'defense_bonus' => 0,
            'max_resource_mult' => 0,
            'production_mult' => 0,
        ];

        $defs = self::cityBuildings();

        foreach ($buildings as $building) {
            $def = $defs[$building->type] ?? null;
            if (!$def) continue;

            foreach ($def['effects'] as $key => $value) {
                $effects[$key] = ($effects[$key] ?? 0) + ($value * $building->level);
            }
        }

        return $effects;
    }

    public static function getBaseBuildingsEffects(Base $base): array
    {
        $buildings = $base->buildings()->get()->keyBy('type');

        $effects = [
            'max_resource_mult' => 0,
            'production_mult' => 0,
            'training_speed' => 0,
            'defense_mult' => 0,
            'troop_speed' => 0,
            'alliance_bonus' => 0,
        ];

        $defs = self::getBaseBuildingsForType($base->type);

        foreach ($buildings as $building) {
            $def = $defs[$building->type] ?? null;
            if (!$def) continue;

            foreach ($def['effects'] as $key => $value) {
                $effects[$key] = ($effects[$key] ?? 0) + ($value * $building->level);
            }
        }

        return $effects;
    }

    public static function getAllPlayerBuildingEffects(int $warId, int $playerId): array
    {
        $effects = [
            'max_resource_mult' => 0,
            'production_mult' => 0,
            'training_speed' => 0,
            'defense_mult' => 0,
            'troop_speed' => 0,
            'alliance_bonus' => 0,
        ];

        $bases = \App\Models\Base::where('war_id', $warId)
            ->where('owner_id', $playerId)
            ->with('buildings')
            ->get();

        foreach ($bases as $base) {
            $baseEffects = self::getBaseBuildingsEffects($base);
            foreach ($effects as $key => &$value) {
                $value += $baseEffects[$key] ?? 0;
            }
        }

        return $effects;
    }
}
