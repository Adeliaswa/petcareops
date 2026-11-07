<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurationFactor extends Model
{
    protected $fillable = [
        'kategori',
        'key',
        'label',
        'multiplier',
        'extra_minutes',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'decimal:3',
            'extra_minutes' => 'integer',
        ];
    }
}