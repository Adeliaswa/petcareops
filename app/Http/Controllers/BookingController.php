<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Customer;
use App\Services\BookingService;
use App\Services\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function identifikasi(
        Request $request,
        PhoneNormalizer $phoneNormalizer
    ): JsonResponse {
        $data = $request->validate([
            'no_wa' => ['required', 'string', 'max:30'],
        ], [
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        try {
            $noWa = $phoneNormalizer->normalize($data['no_wa']);
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'Format nomor WhatsApp tidak valid.',
            ], 422);
        }

        $customer = Customer::query()
            ->where('no_wa', $noWa)
            ->first();

        if (! $customer) {
            return response()->json([
                'dikenal' => false,
                'pets' => [],
            ]);
        }

        return response()->json([
            'dikenal' => true,
            'pets' => $customer->pets()
                ->select(['id', 'nama'])
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function cariPelangganPublik(
    Request $request,
    PhoneNormalizer $phoneNormalizer
): JsonResponse {
    $data = $request->validate([
        'q' => ['required', 'string', 'max:30'],
    ]);

    try {
        $nomor = $phoneNormalizer->normalize($data['q']);
    } catch (InvalidArgumentException) {
        return response()->json(['data' => []]);
    }

    $customer = Customer::query()
        ->where('no_wa', $nomor)
        ->first();

    if (! $customer) {
        return response()->json(['data' => []]);
    }

    return response()->json([
        'data' => [[
            'id' => $customer->id,
            'nama' => $customer->nama,
            'no_wa' => $customer->no_wa,
            'pets' => $customer->pets()
    ->select([
        'id',
        'customer_id',
        'nama',
        'spesies',
        'ras',
        'ukuran',
        'jenis_bulu',
        'perilaku',
    ])
    ->orderBy('nama')
    ->get(),
        ]],
    ]);
}

    public function konfirmasi(
        Request $request,
        BookingService $bookingService
    ): JsonResponse {
        return $this->prosesKonfirmasi(
            $request,
            $bookingService,
            'publik'
        );
    }

    public function konfirmasiWalkIn(
        Request $request,
        BookingService $bookingService
    ): JsonResponse {
        return $this->prosesKonfirmasi(
            $request,
            $bookingService,
            'walk-in'
        );
    }

    private function prosesKonfirmasi(
        Request $request,
        BookingService $bookingService,
        string $sumber
    ): JsonResponse {
        try {
            $appointment = $bookingService->buat(
                $request->all(),
                $sumber
            );

            return response()->json([
                'message' => 'Booking berhasil dibuat.',
                'booking' => [
                    'token' => $appointment->token,
                    'status' => $appointment->status,
                    'sumber' => $appointment->sumber,
                    'pet' => $appointment->pet->nama,
                    'groomer' => $sumber === 'walk-in'
                        ? $appointment->groomer->user->name
                        : null,
                    'mulai' => $appointment->mulai->format('Y-m-d H:i:s'),
                    'selesai_estimasi' => $appointment->selesai_estimasi
                        ->format('Y-m-d H:i:s'),
                    'durasi_estimasi' => $appointment->durasi_estimasi,
                    'layanan' => $appointment->services
                        ->map(fn ($service) => [
                            'nama' => $service->nama_layanan,
                            'harga' => $service->harga,
                            'durasi_dasar' => $service->durasi_dasar,
                        ])
                        ->values(),
                    'status_url' => url('/status/'.$appointment->token),
                ],
            ], 201);
        } catch (BookingException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cariPelanggan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $kataKunci = trim($validated['q']);

        $pelanggan = Customer::query()
            ->where(function ($query) use ($kataKunci) {
                $query->whereRaw(
                    'LOWER(nama) LIKE ?',
                    ['%'.mb_strtolower($kataKunci).'%']
                )->orWhere(
                    'no_wa',
                    'like',
                    '%'.$kataKunci.'%'
                );
            })
            ->limit(10)
            ->get(['id', 'nama', 'no_wa']);

        return response()->json([
            'data' => $pelanggan,
        ]);
    }
}
