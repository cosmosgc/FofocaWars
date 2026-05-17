<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\BattleReport;
use App\Models\Army;
use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{
    public function reports(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $reports = BattleReport::where('war_id', $war->id)
            ->whereHas('attackerArmy', fn($q) => $q->where('owner_id', $player->id))
            ->orWhereHas('defenderCity', fn($q) => $q->where('owner_id', $player->id))
            ->with(['attackerArmy.originCity', 'defenderCity', 'attackerArmy.units.unitType'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'winner' => $r->winner,
                'city' => $r->defenderCity->name,
                'origin' => $r->attackerArmy->originCity->name,
                'details' => $r->details,
                'attacker_losses' => $r->attacker_losses,
                'defender_losses' => $r->defender_losses,
                'created_at' => $r->created_at,
            ]);

        return response()->json($reports);
    }

    public function recent(War $war)
    {
        $since = request('since', now()->subMinutes(5));

        $reports = BattleReport::where('war_id', $war->id)
            ->where('created_at', '>=', $since)
            ->with(['defenderCity', 'attackerArmy.originCity'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'x' => $r->defenderCity->tile_x,
                'y' => $r->defenderCity->tile_y,
                'winner' => $r->winner,
                'attacker_name' => $r->attackerArmy->originCity->name ?? 'Unknown',
                'defender_name' => $r->defenderCity->name ?? 'Unknown',
                'created_at' => $r->created_at,
            ]);

        return response()->json($reports);
    }
}
