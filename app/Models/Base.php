<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Base extends Model
{
    protected $fillable = [
        'war_id', 'owner_id', 'tile_x', 'tile_y',
        'type', 'name', 'level',
    ];

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class, 'owner_id');
    }
}
