<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityBuilding extends Model
{
    protected $table = 'city_buildings';

    protected $fillable = [
        'city_id', 'type', 'level', 'finishes_at',
    ];

    protected function casts(): array
    {
        return [
            'finishes_at' => 'datetime',
            'level' => 'integer',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function isUnderConstruction(): bool
    {
        return $this->finishes_at !== null && $this->finishes_at->isFuture();
    }
}
