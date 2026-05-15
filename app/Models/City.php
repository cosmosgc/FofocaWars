<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [
        'war_id', 'owner_id', 'name',
        'tile_x', 'tile_y', 'population',
        'wood', 'stone', 'food', 'metal',
        'max_wood', 'max_stone', 'max_food', 'max_metal',
    ];

    protected function casts(): array
    {
        return [
            'max_wood' => 'integer',
            'max_stone' => 'integer',
            'max_food' => 'integer',
            'max_metal' => 'integer',
        ];
    }

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class, 'owner_id');
    }
}
