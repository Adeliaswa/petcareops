<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Reminder;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReminderService
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * Membuat pengingat dan mencoba mengirim WhatsApp.
     *
     * $cobaUlangMenunggu digunakan oleh tombol manual.
     */
    public function generateDueReminders(
        bool $cobaUlangMenunggu = false
    ): int {
        $interval = (int) AppSetting::query()
            ->where('key', 'jarak_pengingat_hari')
            ->value('value');

        if ($interval < 1) {
            $interval = 28;
        }

        $jumlahDibuat = 0;

        Pet::query()
            ->select('id')
            ->chunkById(100, function ($pets) use (
                $interval,
                $cobaUlangMenunggu,
                &$jumlahDibuat
            ) {
                foreach ($pets as $pet) {
                    try {
                        $jumlahDibuat += $this->processPet(
                            $pet,
                            $interval,
                            $cobaUlangMenunggu
                        );
                    } catch (Throwable $e) {
                        Log::error('Gagal memproses pengingat grooming.', [
                            'pet_id' => $pet->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $jumlahDibuat;
    }

    private function processPet(
        Pet $pet,
        int $interval,
        bool $cobaUlangMenunggu
    ): int {
        $memilikiAppointmentAktif = Appointment::query()
            ->where('pet_id', $pet->id)
            ->whereIn('status', ['menunggu', 'dikerjakan'])
            ->whereDate('mulai', '>=', today())
            ->exists();

        if ($memilikiAppointmentAktif) {
            Reminder::query()
                ->where('pet_id', $pet->id)
                ->where('status', 'menunggu')
                ->update([
                    'status' => 'ditutup',
                    'ditutup_at' => now(),
                ]);

            return 0;
        }

        $appointmentTerakhir = Appointment::query()
            ->where('pet_id', $pet->id)
            ->whereIn('status', ['selesai', 'dijemput'])
            ->whereHas('groomingRecord')
            ->latest('mulai')
            ->first();

        if (!$appointmentTerakhir) {
            return 0;
        }

        Reminder::query()
            ->where('pet_id', $pet->id)
            ->where('status', 'menunggu')
            ->where(
                'servis_terakhir_id',
                '!=',
                $appointmentTerakhir->id
            )
            ->update([
                'status' => 'ditutup',
                'ditutup_at' => now(),
            ]);

        $tanggalLayanan = $appointmentTerakhir->waktu_selesai_aktual
            ?? $appointmentTerakhir->mulai;

        $tanggalJatuhTempo = $tanggalLayanan
            ->copy()
            ->addDays($interval)
            ->toDateString();

        if ($tanggalJatuhTempo > today()->toDateString()) {
            return 0;
        }

        $reminder = Reminder::firstOrCreate(
            [
                'pet_id' => $pet->id,
                'servis_terakhir_id' => $appointmentTerakhir->id,
            ],
            [
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'status' => 'menunggu',
            ]
        );

        if (
            $reminder->wasRecentlyCreated ||
            (
                $cobaUlangMenunggu &&
                $reminder->status === 'menunggu'
            )
        ) {
            $this->kirimPengingat($reminder);
        }

        return $reminder->wasRecentlyCreated ? 1 : 0;
    }

    private function kirimPengingat(Reminder $reminder): void
    {
        $reminder->loadMissing([
            'pet.customer',
            'servisTerakhir',
        ]);

        $customer = $reminder->pet?->customer;

        if (!$customer || !$customer->no_wa) {
            Log::warning(
                'Pengingat tidak dikirim karena nomor WhatsApp tidak tersedia.',
                ['reminder_id' => $reminder->id]
            );

            return;
        }

        $pesan = $this->whatsAppService
            ->buatPesanPengingat($reminder);

        $hasil = $this->whatsAppService->kirim(
            'pengingat_grooming',
            $customer->no_wa,
            $pesan,
            null,
            $reminder->id
        );

        if ($hasil['status'] === 'berhasil') {
            $reminder->update([
                'status' => 'terkirim',
                'terkirim_at' => now(),
            ]);
        }
    }
}