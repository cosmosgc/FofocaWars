<?php

namespace App\Console\Commands;

use App\Game\Analytics\AnalyticsService;
use App\Models\War;
use Illuminate\Console\Command;

class GameTickAnalytics extends Command
{
    protected $signature = 'game:tick-analytics';
    protected $description = 'Snapshot player stats for analytics';

    public function handle(AnalyticsService $service): void
    {
        $wars = War::where('status', 'running')->get();

        foreach ($wars as $war) {
            $service->snapshot($war);
        }

        $this->info('Analytics snapshots taken for ' . $wars->count() . ' war(s).');
    }
}
