<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Game\Diplomacy\DiplomacyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllianceController extends Controller
{
    public function index(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alliances = Alliance::where('war_id', $war->id)
            ->withCount('members')
            ->with('leader.warUser')
            ->get();

        $myAlliance = AllianceMember::where('war_player_id', $player->id)
            ->with('alliance')
            ->first();

        return view('alliances.index', compact('war', 'player', 'alliances', 'myAlliance'));
    }

    public function show(War $war, Alliance $alliance)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($alliance->war_id !== $war->id, 404);

        $members = AllianceMember::where('alliance_id', $alliance->id)
            ->with('warPlayer.warUser')
            ->get();

        $myMembership = AllianceMember::where('alliance_id', $alliance->id)
            ->where('war_player_id', $player->id)
            ->first();

        $relations = \App\Models\DiplomacyRelation::where('war_id', $war->id)
            ->where(function ($q) use ($alliance) {
                $q->where('alliance_id_1', $alliance->id)
                  ->orWhere('alliance_id_2', $alliance->id);
            })
            ->with('alliance1', 'alliance2')
            ->get();

        return view('alliances.show', compact('war', 'alliance', 'members', 'myMembership', 'relations'));
    }

    public function store(War $war, Request $request, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'tag' => 'required|string|max:10|alpha_num',
            'description' => 'nullable|string|max:500',
        ]);

        $service->createAlliance($war, $player, $validated['name'], $validated['tag'], $validated['description'] ?? null);

        return redirect()->route('alliances.index', $war)->with('success', 'Alliance created.');
    }

    public function join(War $war, Alliance $alliance, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($alliance->war_id !== $war->id, 404);

        $service->joinAlliance($alliance, $player);

        return redirect()->route('alliances.show', [$war, $alliance])->with('success', 'Joined alliance.');
    }

    public function leave(War $war, Alliance $alliance, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($alliance->war_id !== $war->id, 404);

        $service->leaveAlliance($alliance, $player);

        return redirect()->route('alliances.index', $war)->with('success', 'Left alliance.');
    }

    public function kick(War $war, Alliance $alliance, AllianceMember $member, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($alliance->war_id !== $war->id, 404);
        abort_if($alliance->leader_id !== $player->id, 403);

        $service->kickMember($alliance, $member);

        return back()->with('success', 'Member kicked.');
    }

    public function promote(War $war, Alliance $alliance, AllianceMember $member, DiplomacyService $service)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($alliance->war_id !== $war->id, 404);
        abort_if($alliance->leader_id !== $player->id, 403);

        $service->setRole($member, 'officer');

        return back()->with('success', 'Member promoted.');
    }
}
