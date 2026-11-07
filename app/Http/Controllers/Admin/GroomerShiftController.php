<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroomerShiftRequest;
use App\Http\Requests\UpdateGroomerShiftRequest;
use App\Models\Groomer;
use App\Models\GroomerShift;
use App\Services\ScheduleRuleValidator;
use Illuminate\Http\JsonResponse;

class GroomerShiftController extends Controller
{
    public function index(Groomer $groomer): JsonResponse
    {
        $shifts = $groomer->shifts()
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'data' => $shifts,
        ]);
    }

    public function store(
        StoreGroomerShiftRequest $request,
        Groomer $groomer,
        ScheduleRuleValidator $validator
    ): JsonResponse {
        $data = $request->validated();

        $validator->validateShift(
            $groomer->id,
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai']
        );

        $shift = $groomer->shifts()->create($data);

        return response()->json([
            'message' => 'Shift groomer berhasil ditambahkan.',
            'data' => $shift,
        ], 201);
    }

    public function update(
        UpdateGroomerShiftRequest $request,
        Groomer $groomer,
        GroomerShift $shift,
        ScheduleRuleValidator $validator
    ): JsonResponse {
        abort_unless($shift->groomer_id === $groomer->id, 404);

        $data = $request->validated();

        $validator->validateShift(
            $groomer->id,
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $shift->id
        );

        $shift->update($data);

        return response()->json([
            'message' => 'Shift groomer berhasil diperbarui.',
            'data' => $shift->fresh(),
        ]);
    }

    public function destroy(
        Groomer $groomer,
        GroomerShift $shift
    ): JsonResponse {
        abort_unless($shift->groomer_id === $groomer->id, 404);

        $shift->delete();

        return response()->json([
            'message' => 'Shift groomer berhasil dihapus.',
        ]);
    }
}