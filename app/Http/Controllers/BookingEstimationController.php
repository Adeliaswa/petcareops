<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use App\Services\DurationCalculator;
use App\Services\SlotFinderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingEstimationController extends Controller
{
    public function __construct(
        private DurationCalculator $durationCalculator,
        private SlotFinderService $slotFinder
    ) {}

    public function estimasiPublik(Request $request): JsonResponse
    {
        return $this->estimasi($request);
    }

    public function estimasiStaf(Request $request): JsonResponse
    {
        return $this->estimasi($request);
    }

    public function slotPublik(Request $request): JsonResponse
    {
        return $this->cariSlot($request, false);
    }

    public function slotStaf(Request $request): JsonResponse
    {
        return $this->cariSlot($request, true);
    }

    private function estimasi(Request $request): JsonResponse
    {
        $data = $this->validasi($request);

        try {
            $pet = new Pet($data['pet']);

            $hasil = $this->durationCalculator->hitung(
                $pet,
                $data['service_ids'],
                $data['kondisi'] ?? []
            );

            return response()->json([
                'durasi_menit' => $hasil->menit_total,
                'rincian' => $hasil->rincian,
            ]);
        } catch (BusinessRuleException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function cariSlot(
        Request $request,
        bool $untukStaf
    ): JsonResponse {
        $data = $this->validasi($request, true);

        try {
            $pet = new Pet($data['pet']);

            // Hitung ulang durasi agar tidak bergantung
            // pada nilai durasi yang dikirim oleh client.
            $estimasi = $this->durationCalculator->hitung(
                $pet,
                $data['service_ids'],
                $data['kondisi'] ?? []
            );

            $hasil = $this->slotFinder->cari(
                $data['tanggal'],
                $estimasi->menit_total,
                $pet,
                $data['service_ids']
            );

            $slots = $hasil->slots;

            if ($untukStaf && !empty($slots)) {
                $groomerIds = collect($slots)
                    ->pluck('groomer_id')
                    ->unique()
                    ->values();

                $groomers = Groomer::with('user')
                    ->whereIn('id', $groomerIds)
                    ->get()
                    ->keyBy('id');

                $slots = collect($slots)->map(function ($slot) use ($groomers) {
                    $groomer = $groomers->get($slot['groomer_id']);

                    $slot['nama_groomer'] = $groomer?->user?->name;

                    return $slot;
                })->values()->all();
            } else {
                // Data publik tidak menampilkan identitas groomer.
                $slots = collect($slots)->map(function ($slot) {
                    return [
                        'mulai' => $slot['mulai'],
                        'selesai' => $slot['selesai'],
                    ];
                })->values()->all();
            }

            return response()->json([
                'durasi_menit' => $estimasi->menit_total,
                'slots' => $slots,
                'alasan' => $hasil->alasan,
            ]);
        } catch (BusinessRuleException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function validasi(
        Request $request,
        bool $denganTanggal = false
    ): array {
        $aturan = [
            'pet' => ['required', 'array'],
            'pet.spesies' => ['required', 'in:anjing,kucing'],
            'pet.ukuran' => ['required', 'string', 'max:30'],
            'pet.jenis_bulu' => ['required', 'string', 'max:50'],
            'pet.perilaku' => ['required', 'string', 'max:30'],

            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:services,id',
            ],

            'kondisi' => ['sometimes', 'array'],
            'kondisi.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists('duration_factors', 'key')
                    ->where(fn ($query) => $query->where('kategori', 'kondisi')),
            ],
        ];

        if ($denganTanggal) {
            $aturan['tanggal'] = ['required', 'date_format:Y-m-d'];
        }

        return $request->validate($aturan);
    }
}