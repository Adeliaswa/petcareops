<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BookingStatusController extends Controller
{
    /**
     * Menampilkan halaman status booking publik.
     */
    public function show(string $token): View|Response
    {
        return response()
            ->view('booking.status', compact('token'))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Mengambil data status booking dalam format JSON.
     */
    public function data(string $token): JsonResponse
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
                    'pet' => $appointment->pet?->nama ?? '-',
                    'layanan' => $appointment->services->map(
                        fn ($service) => [
                            'nama' => $service->nama_layanan,
                            'harga' => $service->harga,
                        ]
                    )->values(),
                    'mulai' => $appointment->mulai?->format('Y-m-d H:i:s'),
                    'selesai_estimasi' => $appointment->selesai_estimasi
                        ?->format('Y-m-d H:i:s'),
                    'durasi_estimasi' => (int) $appointment->durasi_estimasi,
                    'status' => $appointment->status,
                    'token' => $appointment->token,
                ],
            ], 200)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}