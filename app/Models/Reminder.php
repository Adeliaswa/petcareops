<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    protected $fillable = [
        'pet_id',
        'servis_terakhir_id',
        'tanggal_jatuh_tempo',
        'status',
        'terkirim_at',
        'ditutup_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jatuh_tempo' => 'date',
            'terkirim_at' => 'datetime',
            'ditutup_at' => 'datetime',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function servisTerakhir(): BelongsTo
    {
        return $this->belongsTo(
            Appointment::class,
            'servis_terakhir_id'
        );
    }
}