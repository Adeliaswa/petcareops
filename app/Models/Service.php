<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama',
        'deskripsi',
        'durasi_dasar',
        'harga',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'durasi_dasar' => 'integer',
            'harga' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}