<?php

namespace App\Services;

use App\Models\GroomerShift;
use Illuminate\Validation\ValidationException;

class ScheduleRuleValidator
{
    public function validateShift(
        int $groomerId,
        int $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeShiftId = null
    ): void {
        $overlap = GroomerShift::query()
            ->where('groomer_id', $groomerId)
            ->where('hari', $hari)
            ->when(
                $excludeShiftId,
                fn ($query) => $query->where('id', '!=', $excludeShiftId)
            )
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Shift bertumpuk dengan jadwal groomer yang sudah ada.',
            ]);
        }
    }
}