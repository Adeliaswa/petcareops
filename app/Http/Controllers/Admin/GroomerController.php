<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGroomerRequest;
use App\Models\Groomer;
use Illuminate\Http\JsonResponse;

class GroomerController extends Controller
{
    public function index(): JsonResponse
    {
        $groomers = Groomer::query()
            ->with('user:id,name,email,is_active')
            ->latest()
            ->paginate(15);

        return response()->json($groomers);
    }

    public function show(Groomer $groomer): JsonResponse
    {
        $groomer->load('user:id,name,email,is_active');

        return response()->json([
            'data' => $groomer,
        ]);
    }

    public function update(
        UpdateGroomerRequest $request,
        Groomer $groomer
    ): JsonResponse {
        $groomer->update($request->validated());

        return response()->json([
            'message' => 'Profil groomer berhasil diperbarui.',
            'data' => $groomer->fresh()->load('user:id,name,email,is_active'),
        ]);
    }
}