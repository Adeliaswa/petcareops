<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    protected $fillable = [
        'pet_id',
        'groomer_id',
        'mulai',
        'selesai_estimasi',
        'durasi_estimasi',
        'status',
        'token',
        'rincian_estimasi',
        'sumber',
        'catatan_pelanggan',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai_estimasi' => 'datetime',
            'durasi_estimasi' => 'integer',
            'rincian_estimasi' => 'array',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function groomer(): BelongsTo
    {
        return $this->belongsTo(Groomer::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(AppointmentService::class);
    }
}
