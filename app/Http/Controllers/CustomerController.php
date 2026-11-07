<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        $customers = Customer::query()
            ->withCount('pets')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(nama) LIKE ?', [
                        '%' . strtolower($search) . '%',
                    ])->orWhere('no_wa', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json([
            'message' => 'Data pemilik berhasil ditambahkan.',
            'data' => $customer,
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load('pets');

        return response()->json([
            'data' => $customer,
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ): JsonResponse {
        $customer->update($request->validated());

        return response()->json([
            'message' => 'Data pemilik berhasil diperbarui.',
            'data' => $customer->fresh(),
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->pets()->exists()) {
            return response()->json([
                'message' => 'Pemilik tidak dapat dihapus karena masih memiliki data hewan.',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Data pemilik berhasil dihapus.',
        ]);
    }
}