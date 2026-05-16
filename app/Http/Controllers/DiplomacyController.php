<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\DiplomacyRelation;
use App\Game\Diplomacy\DiplomacyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiplomacyController extends Controller
{
    public function setRelation(War $war, Alliance $alliance, Request $request, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $myMembership = AllianceMember::where('war_player_id', $player->id)
            ->whereIn('role', ['leader', 'officer'])
            ->first();

        abort_if(!$myMembership || $myMembership->alliance_id !== $alliance->id, 403);

        $validated = $request->validate([
            'target_id' => 'required|exists:alliances,id',
            'type' => 'required|in:allied,neutral,war,trade_pact,non_aggression',
        ]);

        $target = Alliance::where('war_id', $war->id)->findOrFail($validated['target_id']);
        abort_if($target->id === $alliance->id, 400);

        $service->setRelation($alliance, $target, $validated['type'], $player);

        return back()->with('success', 'Diplomacy updated.');
    }
}
