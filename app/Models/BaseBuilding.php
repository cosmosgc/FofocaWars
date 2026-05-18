<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseBuilding extends Model
{
    protected $table = 'base_buildings';

    protected $fillable = [
        'base_id', 'type', 'level', 'pos_x', 'pos_y', 'finishes_at',
    ];

    protected function casts(): array
    {
        return [
            'finishes_at' => 'datetime',
            'level' => 'integer',
            'pos_x' => 'integer',
            'pos_y' => 'integer',
        ];
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    public function isUnderConstruction(): bool
    {
        return $this->finishes_at !== null && $this->finishes_at->isFuture();
    }
}
