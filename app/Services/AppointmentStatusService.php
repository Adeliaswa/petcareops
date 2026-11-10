<?php

namespace App\Services;

use App\Events\GroomingSelesai;
use App\Events\StatusDiubah;
use App\Models\Appointment;
use App\Models\GroomingRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentStatusService
{
    public function mulai(Appointment $appointment, User $user): Appointment
    {
        return DB::transaction(function () use ($appointment, $user) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $groomer = $user->groomer;

            if (!$groomer || $appointment->groomer_id !== $groomer->id) {
                abort(403, 'Anda tidak memiliki akses ke appointment ini.');
            }

            if ($appointment->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => 'Appointment tidak dapat dimulai karena statusnya bukan menunggu.',
                ]);
            }

            if (!$appointment->mulai->isToday()) {
                throw ValidationException::withMessages([
                    'status' => 'Appointment hanya dapat dimulai pada tanggal jadwalnya.',
                ]);
            }

            $sudahAdaTugas = Appointment::query()
                ->where('groomer_id', $groomer->id)
                ->where('status', 'dikerjakan')
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($sudahAdaTugas) {
                throw ValidationException::withMessages([
                    'status' => 'Groomer masih memiliki appointment yang sedang dikerjakan.',
                ]);
            }

            $statusSebelumnya = $appointment->status;

            $appointment->update([
                'status' => 'dikerjakan',
                'waktu_mulai_aktual' => now(),
            ]);

            $appointment->refresh();

            DB::afterCommit(function () use ($appointment, $statusSebelumnya) {
                StatusDiubah::dispatch(
                    $appointment,
                    $statusSebelumnya,
                    $appointment->status
                );
            });

            return $appointment;
        });
    }

    public function batalkan(
        Appointment $appointment,
        string $alasan
    ): Appointment {
        return DB::transaction(function () use ($appointment, $alasan) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            if ($appointment->status !== 'menunggu') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya appointment berstatus menunggu yang dapat dibatalkan.',
                ]);
            }

            $statusSebelumnya = $appointment->status;

            $appointment->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $alasan,
            ]);

            $appointment->refresh();

            DB::afterCommit(function () use ($appointment, $statusSebelumnya) {
                StatusDiubah::dispatch(
                    $appointment,
                    $statusSebelumnya,
                    $appointment->status
                );
            });

            return $appointment;
        });
    }

    public function selesai(
        Appointment $appointment,
        User $user,
        string $catatanGrooming
    ): Appointment {
        return DB::transaction(function () use (
            $appointment,
            $user,
            $catatanGrooming
        ) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $groomer = $user->groomer;

            if (!$groomer || $appointment->groomer_id !== $groomer->id) {
                abort(403, 'Anda tidak berhak menyelesaikan appointment ini.');
            }

            if ($appointment->status !== 'dikerjakan') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya appointment yang sedang dikerjakan yang dapat diselesaikan.',
                ]);
            }

            if (!$appointment->waktu_mulai_aktual) {
                throw ValidationException::withMessages([
                    'status' => 'Waktu mulai grooming belum tercatat.',
                ]);
            }

            if ($appointment->waktu_mulai_aktual->isAfter(now())) {
                throw ValidationException::withMessages([
                    'status' => 'Waktu mulai grooming tidak valid.',
                ]);
            }

            if ($appointment->groomingRecord()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Catatan grooming untuk appointment ini sudah tersedia.',
                ]);
            }

            $waktuSelesai = now();

            $durasiAktual = (int) $appointment->waktu_mulai_aktual
                ->diffInMinutes($waktuSelesai);

            if ($durasiAktual < 1 || $durasiAktual > 600) {
                throw ValidationException::withMessages([
                    'durasi_aktual' => 'Durasi grooming harus antara 1 sampai 600 menit.',
                ]);
            }

            $statusSebelumnya = $appointment->status;

            GroomingRecord::create([
                'appointment_id' => $appointment->id,
                'durasi_aktual' => $durasiAktual,
                'catatan_grooming' => $catatanGrooming,
            ]);

            $appointment->update([
                'status' => 'selesai',
                'waktu_selesai_aktual' => $waktuSelesai,
            ]);

            $appointment->refresh()->load('groomingRecord');

            DB::afterCommit(function () use ($appointment, $statusSebelumnya) {
                StatusDiubah::dispatch(
                    $appointment,
                    $statusSebelumnya,
                    $appointment->status
                );

                GroomingSelesai::dispatch($appointment);
            });

            return $appointment;
        });
    }
}