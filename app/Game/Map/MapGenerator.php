<?php

namespace App\Game\Map;

use App\Models\War;
use App\Models\Tile;
use Illuminate\Support\Facades\DB;

class MapGenerator
{
    private array $terrainWeights = [
        'plain' => 40,
        'forest' => 20,
        'mountain' => 15,
        'water' => 15,
        'desert' => 10,
    ];

    private array $resourceByTerrain = [
        'plain' => ['food', 'stone'],
        'forest' => ['wood', 'food'],
        'mountain' => ['metal', 'stone'],
        'water' => ['food'],
        'desert' => ['stone', 'metal'],
    ];

    public function generate(War $war): void
    {
        $tiles = [];
        $now = now();

        for ($x = 0; $x < $war->map_width; $x++) {
            for ($y = 0; $y < $war->map_height; $y++) {
                $terrain = $this->randomTerrain();
                $resource = $this->randomResource($terrain);

                $tiles[] = [
                    'war_id' => $war->id,
                    'x' => $x,
                    'y' => $y,
                    'terrain_type' => $terrain,
                    'resource_type' => $resource,
                    'owner_id' => null,
                    'structure_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $chunks = array_chunk($tiles, 500);
        foreach ($chunks as $chunk) {
            Tile::insert($chunk);
        }
    }

    private function randomTerrain(): string
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;

        foreach ($this->terrainWeights as $terrain => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $terrain;
            }
        }

        return 'plain';
    }

    private function randomResource(string $terrain): ?string
    {
        $resources = $this->resourceByTerrain[$terrain] ?? [];

        if (empty($resources)) {
            return null;
        }

        if (mt_rand(1, 100) <= 60) {
            return $resources[array_rand($resources)];
        }

        return null;
    }
}
