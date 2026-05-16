<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceMember extends Model
{
    public $timestamps = false;

    protected $fillable = ['alliance_id', 'war_player_id', 'role', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }

    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    public function warPlayer(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class);
    }
}
