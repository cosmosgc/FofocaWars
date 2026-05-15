<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tile extends Model
{
    protected $fillable = [
        'war_id', 'x', 'y', 'terrain_type',
        'owner_id', 'resource_type', 'structure_id',
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
