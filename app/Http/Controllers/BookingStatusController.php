<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class BookingStatusController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $appointment = Appointment::query()
            ->with([
                'pet:id,nama',
                'services:id,appointment_id,nama_layanan,durasi_dasar,harga',
            ])
            ->where('token', $token)
            ->first();

        if (! $appointment) {
            return response()
                ->json([
                    'message' => 'Link tidak valid.',
                ], 404)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()
            ->json([
                'booking' => [
                    'pet' => $appointment->pet->nama,
                    'layanan' => $appointment->services->map(
                        fn ($service) => [
                            'nama' => $service->nama_layanan,
                            'harga' => $service->harga,
                        ]
                    )->values(),
                    'mulai' => $appointment->mulai->format('Y-m-d H:i:s'),
                    'selesai_estimasi' => $appointment->selesai_estimasi
                        ->format('Y-m-d H:i:s'),
                    'durasi_estimasi' => $appointment->durasi_estimasi,
                    'status' => $appointment->status,
                ],
            ], 200)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
