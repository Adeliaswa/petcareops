<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'nama',
        'spesies',
        'ras',
        'ukuran',
        'jenis_bulu',
        'perilaku',
        'alergi',
        'catatan_kesehatan',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}