<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroomerTimeOffRequest;
use App\Http\Requests\UpdateGroomerTimeOffRequest;
use App\Models\Groomer;
use App\Models\GroomerTimeOff;
use Illuminate\Http\JsonResponse;

class GroomerTimeOffController extends Controller
{

    public function indexAll(): JsonResponse
    {
        $timeOffs = GroomerTimeOff::with('groomer.user:id,name')
            ->orderBy('tanggal_mulai')
            ->get();

        return response()->json([
            'data' => $timeOffs,
        ]);
    }
    public function index(Groomer $groomer): JsonResponse
    {
        $timeOffs = $groomer->timeOffs()
            ->orderBy('tanggal_mulai')
            ->get();

        return response()->json([
            'data' => $timeOffs,
        ]);
    }

    public function store(
        StoreGroomerTimeOffRequest $request,
        Groomer $groomer
    ): JsonResponse {
        $timeOff = $groomer->timeOffs()->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Jadwal libur groomer berhasil ditambahkan.',
            'data' => $timeOff,
        ], 201);
    }

    public function update(
        UpdateGroomerTimeOffRequest $request,
        Groomer $groomer,
        GroomerTimeOff $timeOff
    ): JsonResponse {
        abort_unless($timeOff->groomer_id === $groomer->id, 404);

        $timeOff->update($request->validated());

        return response()->json([
            'message' => 'Jadwal libur groomer berhasil diperbarui.',
            'data' => $timeOff->fresh(),
        ]);
    }

    public function destroy(
        Groomer $groomer,
        GroomerTimeOff $timeOff
    ): JsonResponse {
        abort_unless($timeOff->groomer_id === $groomer->id, 404);

        $timeOff->delete();

        return response()->json([
            'message' => 'Jadwal libur groomer berhasil dihapus.',
        ]);
    }
}