<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentStatusController extends Controller
{
    public function mulai(
        Request $request,
        Appointment $appointment,
        AppointmentStatusService $service
    ): RedirectResponse {
        $service->mulai($appointment, $request->user());

        return back()->with(
            'success',
            'Appointment berhasil dimulai.'
        );
    }

    public function selesai(
    Request $request,
    Appointment $appointment,
    AppointmentStatusService $service
): RedirectResponse {
    $validated = $request->validate([
        'catatan_grooming' => ['required', 'string', 'max:2000'],
    ]);

    $service->selesai(
        $appointment,
        $request->user(),
        $validated['catatan_grooming']
    );

    return back()->with('success', 'Grooming berhasil diselesaikan.');
}

    public function batalkan(
        Request $request,
        Appointment $appointment,
        AppointmentStatusService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'alasan_batal' => ['required', 'string', 'max:255'],
        ]);

        $service->batalkan(
            $appointment,
            $validated['alasan_batal']
        );

        return back()->with(
            'success',
            'Appointment berhasil dibatalkan.'
        );
    }
}