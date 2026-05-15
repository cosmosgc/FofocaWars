<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingQueue extends Model
{
    protected $table = 'training_queue';

    protected $fillable = ['city_id', 'unit_type_id', 'quantity', 'finishes_at'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'finishes_at' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }
}
