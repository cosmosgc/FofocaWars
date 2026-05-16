<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = ['conversation_id', 'war_player_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function warPlayer(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class);
    }
}
