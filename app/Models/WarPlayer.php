<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarPlayer extends Model
{
    use HasFactory;
    protected $table = 'war_players';

    protected $fillable = [
        'war_id', 'user_id', 'joined_at', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'owner_id');
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(Tile::class, 'owner_id');
    }
}
