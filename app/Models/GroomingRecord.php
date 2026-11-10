<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroomingRecord extends Model
{
    protected $fillable = [
        'appointment_id',
        'durasi_aktual',
        'catatan_grooming',
    ];

    protected function casts(): array
    {
        return [
            'durasi_aktual' => 'integer',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}