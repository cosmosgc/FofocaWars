<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitType extends Model
{
    protected $fillable = [
        'war_id', 'name', 'role', 'attack', 'defense', 'speed',
        'wood_cost', 'stone_cost', 'food_cost', 'metal_cost',
        'population_cost', 'tier', 'image',
    ];

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }
}
