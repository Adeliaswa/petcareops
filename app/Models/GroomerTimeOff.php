<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroomerTimeOff extends Model
{
    protected $table = 'groomer_time_off';

    protected $fillable = [
        'groomer_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function groomer(): BelongsTo
    {
        return $this->belongsTo(Groomer::class);
    }
}