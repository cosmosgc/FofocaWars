<?php

namespace App\Console\Commands;

use App\Models\TrainingQueue;
use App\Models\Unit;
use Illuminate\Console\Command;

class TrainingTick extends Command
{
    protected $signature = 'game:tick-training';
    protected $description = 'Complete finished training queue entries';

    public function handle(): void
    {
        $entries = TrainingQueue::where('finishes_at', '<=', now())->get();
        $count = 0;

        foreach ($entries as $entry) {
            $unit = Unit::firstOrCreate(
                ['city_id' => $entry->city_id, 'unit_type_id' => $entry->unit_type_id],
                ['quantity' => 0]
            );
            $unit->increment('quantity', $entry->quantity);
            $entry->delete();
            $count++;
        }

        $this->info("Completed {$count} training entries.");
    }
}
