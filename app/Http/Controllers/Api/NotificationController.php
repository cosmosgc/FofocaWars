<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $unread = Notification::where('war_player_id', $player->id)
            ->whereNull('read_at')
            ->latest()
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'created_at' => $n->created_at,
            ]);

        $count = $unread->count();

        return response()->json([
            'count' => $count,
            'notifications' => $unread,
        ]);
    }

    public function read(War $war, Notification $notification)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($notification->war_player_id !== $player->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
