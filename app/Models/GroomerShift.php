<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroomerShift extends Model
{
    protected $fillable = [
        'groomer_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    protected function casts(): array
    {
        return [
            'hari' => 'integer',
        ];
    }

    public function groomer(): BelongsTo
    {
        return $this->belongsTo(Groomer::class);
    }
}