<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Army extends Model
{
    protected $fillable = [
        'war_id', 'owner_id', 'origin_city_id', 'target_city_id',
        'status', 'arrival_at',
    ];

    protected function casts(): array
    {
        return ['arrival_at' => 'datetime'];
    }

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class, 'owner_id');
    }

    public function originCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    public function targetCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'target_city_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ArmyUnit::class);
    }
}
