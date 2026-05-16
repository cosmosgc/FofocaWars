<?php

namespace App\Game\Diplomacy;

use App\Models\War;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\WarPlayer;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class DiplomacyService
{
    public function createAlliance(War $war, WarPlayer $leader, string $name, string $tag, ?string $description = null): Alliance
    {
        return DB::transaction(function () use ($war, $leader, $name, $tag, $description) {
            $alliance = Alliance::create([
                'war_id' => $war->id,
                'name' => $name,
                'tag' => strtoupper($tag),
                'description' => $description,
                'leader_id' => $leader->id,
            ]);

            AllianceMember::create([
                'alliance_id' => $alliance->id,
                'war_player_id' => $leader->id,
                'role' => 'leader',
                'joined_at' => now(),
            ]);

            return $alliance;
        });
    }

    public function joinAlliance(Alliance $alliance, WarPlayer $player): AllianceMember
    {
        return DB::transaction(function () use ($alliance, $player) {
            return AllianceMember::create([
                'alliance_id' => $alliance->id,
                'war_player_id' => $player->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        });
    }

    public function leaveAlliance(Alliance $alliance, WarPlayer $player): void
    {
        DB::transaction(function () use ($alliance, $player) {
            AllianceMember::where('alliance_id', $alliance->id)
                ->where('war_player_id', $player->id)
                ->delete();

            if ($alliance->leader_id === $player->id) {
                $newLeader = AllianceMember::where('alliance_id', $alliance->id)
                    ->where('role', 'officer')
                    ->first();

                if (!$newLeader) {
                    $newLeader = AllianceMember::where('alliance_id', $alliance->id)
                        ->where('role', 'member')
                        ->first();
                }

                if ($newLeader) {
                    $newLeader->update(['role' => 'leader']);
                    $alliance->update(['leader_id' => $newLeader->war_player_id]);
                } else {
                    $alliance->delete();
                }
            }
        });
    }

    public function kickMember(Alliance $alliance, AllianceMember $member): void
    {
        if ($member->role === 'leader') return;
        $member->delete();
    }

    public function setRole(AllianceMember $member, string $role): void
    {
        $member->update(['role' => $role]);
    }

    public function setRelation(Alliance $a1, Alliance $a2, string $type, ?WarPlayer $proposer = null): DiplomacyRelation
    {
        return \App\Models\DiplomacyRelation::updateOrCreate(
            [
                'war_id' => $a1->war_id,
                'alliance_id_1' => $a1->id,
                'alliance_id_2' => $a2->id,
            ],
            [
                'type' => $type,
                'proposed_by' => $proposer?->id,
            ]
        );
    }
}
