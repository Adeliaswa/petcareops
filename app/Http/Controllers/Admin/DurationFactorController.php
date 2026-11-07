<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDurationFactorRequest;
use App\Http\Requests\UpdateDurationFactorRequest;
use App\Models\DurationFactor;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;

class DurationFactorController extends Controller
{
    public function index(): JsonResponse
    {
        $factors = DurationFactor::query()
            ->orderBy('kategori')
            ->orderBy('key')
            ->paginate(15);

        return response()->json($factors);
    }

    public function store(StoreDurationFactorRequest $request): JsonResponse
    {
        $factor = DurationFactor::create($request->validated());

        return response()->json([
            'message' => 'Faktor durasi berhasil ditambahkan.',
            'data' => $factor,
        ], 201);
    }

    public function show(DurationFactor $factor): JsonResponse
    {
        return response()->json([
            'data' => $factor,
        ]);
    }

    public function update(
        UpdateDurationFactorRequest $request,
        DurationFactor $factor
    ): JsonResponse {
        $factor->update($request->validated());

        return response()->json([
            'message' => 'Faktor durasi berhasil diperbarui.',
            'data' => $factor->fresh(),
        ]);
    }

    public function destroy(DurationFactor $factor): JsonResponse
    {
        $petColumn = match ($factor->kategori) {
            'ukuran' => 'ukuran',
            'jenis_bulu' => 'jenis_bulu',
            'perilaku' => 'perilaku',
            default => null,
        };

        if (
            $petColumn !== null &&
            Pet::withTrashed()
                ->where($petColumn, $factor->key)
                ->exists()
        ) {
            return response()->json([
                'message' => 'Faktor durasi sudah digunakan oleh data hewan dan tidak dapat dihapus.',
            ], 422);
        }

        $factor->delete();

        return response()->json([
            'message' => 'Faktor durasi berhasil dihapus.',
        ]);
    }
}