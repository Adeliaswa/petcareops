<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'appointment_id',
        'nomor_invoice',
        'total',
        'diterbitkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'diterbitkan_pada' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalDibayarAttribute(): int
    {
        return (int) $this->payments()->sum('nominal');
    }

    public function getSisaTagihanAttribute(): int
    {
        return max(0, $this->total - $this->total_dibayar);
    }

    public function getStatusPembayaranAttribute(): string
    {
        if ($this->total_dibayar <= 0) {
            return 'belum_bayar';
        }

        if ($this->total_dibayar < $this->total) {
            return 'sebagian';
        }

        return 'lunas';
    }
}