<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiplomacyRelation extends Model
{
    protected $fillable = [
        'war_id', 'alliance_id_1', 'alliance_id_2',
        'type', 'proposed_by', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function alliance1(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'alliance_id_1');
    }

    public function alliance2(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'alliance_id_2');
    }
}
