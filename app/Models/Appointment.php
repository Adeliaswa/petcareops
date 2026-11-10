<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'alasan_batal',
        'waktu_mulai_aktual',
        'waktu_selesai_aktual',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai_estimasi' => 'datetime',
            'durasi_estimasi' => 'integer',
            'rincian_estimasi' => 'array',
            'alasan_batal' => 'string',
            'waktu_mulai_aktual' => 'datetime',
            'waktu_selesai_aktual' => 'datetime',
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

    public function groomingRecord(): HasOne
    {
        return $this->hasOne(GroomingRecord::class);
    }

    public function reminder(): HasOne
{
    return $this->hasOne(
        Reminder::class,
        'servis_terakhir_id'
    );
}
public function invoice(): HasOne
{
    return $this->hasOne(Invoice::class);
}
}
