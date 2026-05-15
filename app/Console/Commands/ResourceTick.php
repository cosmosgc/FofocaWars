<?php

namespace App\Console\Commands;

use App\Game\Economy\ResourceService;
use App\Models\War;
use Illuminate\Console\Command;

class ResourceTick extends Command
{
    protected $signature = 'game:tick-resources';
    protected $description = 'Generate resources for all active wars';

    public function handle(ResourceService $service): void
    {
        $wars = War::where('status', 'running')->get();

        foreach ($wars as $war) {
            $service->tickWar($war);
        }

        $this->info('Resources ticked for ' . $wars->count() . ' war(s).');
    }
}
