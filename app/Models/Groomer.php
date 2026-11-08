<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groomer extends Model
{
    protected $fillable = [
        'user_id',
        'level_skill',
        'bisa_kucing',
        'bisa_agresif',
    ];

    protected function casts(): array
    {
        return [
            'bisa_kucing' => 'boolean',
            'bisa_agresif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(GroomerShift::class);
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(GroomerTimeOff::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
