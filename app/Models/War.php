<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class War extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'theme', 'map_width', 'map_height',
        'resource_multiplier', 'troop_speed_multiplier',
        'construction_speed', 'max_bases_per_player',
        'status', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'resource_multiplier' => 'decimal:2',
            'troop_speed_multiplier' => 'decimal:2',
            'construction_speed' => 'decimal:2',
        ];
    }

    public function themeData(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme', 'name');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'war_players')
            ->withPivot('joined_at', 'last_active_at');
    }

    public function warPlayers(): HasMany
    {
        return $this->hasMany(WarPlayer::class);
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(Tile::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function alliances(): HasMany
    {
        return $this->hasMany(Alliance::class);
    }
}
