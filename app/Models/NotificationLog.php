<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'jenis',
        'appointment_id',
        'reminder_id',
        'nomor_tujuan',
        'pesan',
        'status',
        'respons',
        'terkirim_at',
    ];

    protected function casts(): array
    {
        return [
            'terkirim_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }
}
