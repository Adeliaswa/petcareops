<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        $pets = Pet::query()
            ->with('customer:id,nama,no_wa')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(nama) LIKE ?', [
                        '%' . strtolower($search) . '%',
                    ])
                    ->orWhereRaw('LOWER(spesies) LIKE ?', [
                        '%' . strtolower($search) . '%',
                    ])
                    ->orWhereRaw('LOWER(ras) LIKE ?', [
                        '%' . strtolower($search) . '%',
                    ])
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->whereRaw('LOWER(nama) LIKE ?', [
                            '%' . strtolower($search) . '%',
                        ]);
                    });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json($pets);
    }

    public function store(StorePetRequest $request): JsonResponse
    {
        $pet = Pet::create($request->validated());

        return response()->json([
            'message' => 'Data hewan berhasil ditambahkan.',
            'data' => $pet->load('customer:id,nama,no_wa'),
        ], 201);
    }

    public function show(Pet $pet): JsonResponse
    {
        $pet->load('customer:id,nama,no_wa');

        return response()->json([
            'data' => $pet,
        ]);
    }

    public function update(
        UpdatePetRequest $request,
        Pet $pet
    ): JsonResponse {
        $pet->update($request->validated());

        return response()->json([
            'message' => 'Data hewan berhasil diperbarui.',
            'data' => $pet->fresh()->load('customer:id,nama,no_wa'),
        ]);
    }

    public function destroy(Pet $pet): JsonResponse
    {
        $pet->delete();

        return response()->json([
            'message' => 'Data hewan berhasil dihapus.',
        ]);
    }
}