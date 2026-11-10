<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BookingStatusController extends Controller
{
    /**
     * Halaman status booking publik. Token berfungsi sebagai akses privat.
     */
    public function show(string $token): View
    {
        return view('booking.status', compact('token'));
    }

    /**
     * Data status untuk dimuat oleh halaman status.
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
            return response()->json([
                'message' => 'Link status booking tidak valid atau sudah tidak tersedia.',
            ], 404)->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()->json([
            'booking' => [
                'pet' => $appointment->pet?->nama ?? '-',
                'layanan' => $appointment->services->map(fn ($service) => [
                    'nama' => $service->nama_layanan,
                    'harga' => (float) $service->harga,
                ])->values(),
                'mulai' => $appointment->mulai?->format('Y-m-d H:i:s'),
                'selesai_estimasi' => $appointment->selesai_estimasi?->format('Y-m-d H:i:s'),
                'durasi_estimasi' => (int) $appointment->durasi_estimasi,
                'status' => $appointment->status,
                'token' => $appointment->token,
            ],
        ], 200)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
