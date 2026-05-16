<?php

namespace App\Http\Controllers;

use App\Models\War;
use App\Models\WarPlayer;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(War $war)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversations = Conversation::where('war_id', $war->id)
            ->whereHas('participants', fn($q) => $q->where('war_player_id', $player->id))
            ->with(['participants.warPlayer.warUser', 'messages.sender.warUser'])
            ->latest('updated_at')
            ->get();

        $players = WarPlayer::where('war_id', $war->id)
            ->where('id', '!=', $player->id)
            ->with('warUser')
            ->get();

        return view('messages.index', compact('war', 'player', 'conversations', 'players'));
    }

    public function show(War $war, Conversation $conversation)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_if($conversation->war_id !== $war->id, 404);

        $isParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('war_player_id', $player->id)
            ->exists();

        abort_unless($isParticipant, 403);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('sender.warUser')
            ->oldest()
            ->get();

        $participants = ConversationParticipant::where('conversation_id', $conversation->id)
            ->with('warPlayer.warUser')
            ->get();

        return view('messages.show', compact('war', 'conversation', 'messages', 'player', 'participants'));
    }

    public function send(War $war, Request $request)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => $player->id,
            'content' => $validated['content'],
        ]);

        Conversation::find($validated['conversation_id'])->touch();

        return redirect()->route('messages.show', [$war, $validated['conversation_id']]);
    }

    public function new(War $war, Request $request)
    {
        $player = WarPlayer::where('war_id', $war->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'recipient_id' => 'required|exists:war_players,id',
            'content' => 'required|string|max:2000',
        ]);

        $existing = Conversation::where('war_id', $war->id)
            ->whereHas('participants', fn($q) => $q->where('war_player_id', $player->id))
            ->whereHas('participants', fn($q) => $q->where('war_player_id', $validated['recipient_id']))
            ->first();

        if (!$existing) {
            $existing = Conversation::create(['war_id' => $war->id]);
            ConversationParticipant::create(['conversation_id' => $existing->id, 'war_player_id' => $player->id]);
            ConversationParticipant::create(['conversation_id' => $existing->id, 'war_player_id' => $validated['recipient_id']]);
        }

        Message::create([
            'conversation_id' => $existing->id,
            'sender_id' => $player->id,
            'content' => $validated['content'],
        ]);

        return redirect()->route('messages.show', [$war, $existing->id]);
    }
}
