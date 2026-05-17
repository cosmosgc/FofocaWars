<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['name', 'label', 'description', 'is_default', 'config'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'config' => 'array',
        ];
    }

    public function wars(): HasMany
    {
        return $this->hasMany(War::class, 'theme', 'name');
    }
}
