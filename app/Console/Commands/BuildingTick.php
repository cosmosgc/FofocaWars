<?php

namespace App\Console\Commands;

use App\Game\Building\ConstructionService;
use Illuminate\Console\Command;

class BuildingTick extends Command
{
    protected $signature = 'game:tick-buildings';
    protected $description = 'Complete finished building constructions';

    public function handle(ConstructionService $service): void
    {
        $cityCount = $service->completeCityConstructions();
        $baseCount = $service->completeBaseConstructions();
        $this->info("Completed {$cityCount} city buildings and {$baseCount} base buildings.");
    }
}
