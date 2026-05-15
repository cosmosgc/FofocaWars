<?php

namespace App\Console\Commands;

use App\Game\Army\ArmyService;
use App\Models\War;
use Illuminate\Console\Command;

class ArmyTick extends Command
{
    protected $signature = 'game:tick-armies';
    protected $description = 'Resolve army arrivals for all active wars';

    public function handle(ArmyService $service): void
    {
        $wars = War::where('status', 'running')->get();
        $count = 0;

        foreach ($wars as $war) {
            $service->tickArrivals($war);
            $count++;
        }

        $this->info("Army arrivals ticked for {$count} war(s).");
    }
}
