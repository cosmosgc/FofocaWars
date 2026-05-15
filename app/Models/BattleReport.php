<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleReport extends Model
{
    protected $fillable = [
        'war_id', 'attacker_army_id', 'defender_city_id',
        'winner', 'attacker_losses', 'defender_losses', 'details',
    ];

    protected function casts(): array
    {
        return [
            'attacker_losses' => 'array',
            'defender_losses' => 'array',
            'details' => 'array',
        ];
    }

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function attackerArmy(): BelongsTo
    {
        return $this->belongsTo(Army::class, 'attacker_army_id');
    }

    public function defenderCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'defender_city_id');
    }
}
