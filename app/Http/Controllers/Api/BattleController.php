<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\BattleReport;
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
}
