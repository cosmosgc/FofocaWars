<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alliance extends Model
{
    protected $fillable = ['war_id', 'name', 'tag', 'description', 'leader_id'];

    public function war(): BelongsTo
    {
        return $this->belongsTo(War::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(WarPlayer::class, 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(AllianceMember::class);
    }

    public function diploRels1(): HasMany
    {
        return $this->hasMany(DiplomacyRelation::class, 'alliance_id_1');
    }

    public function diploRels2(): HasMany
    {
        return $this->hasMany(DiplomacyRelation::class, 'alliance_id_2');
    }
}
