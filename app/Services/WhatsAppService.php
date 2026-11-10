<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Reminder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public function kirim(
        string $jenis,
        string $nomor,
        string $pesan,
        ?int $appointmentId = null,
        ?int $reminderId = null
    ): array {
        $nomor = $this->normalisasiNomor($nomor);
        $waLink = $this->buatLinkManual($nomor, $pesan);
        $token = config('services.fonnte.token');

        if (empty($token)) {
            $this->catatLog(
                $jenis,
                $appointmentId,
                $reminderId,
                $nomor,
                $pesan,
                'fallback',
                'Token Fonnte belum dikonfigurasi.'
            );

            return [
                'status' => 'fallback',
                'wa_link' => $waLink,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])
                ->timeout(5)
                ->asForm()
                ->post(config('services.fonnte.url'), [
                    'target' => $nomor,
                    'message' => $pesan,
                ]);

            $hasil = $response->json();

            if (
                $response->successful() &&
                ($hasil['status'] ?? false) === true
            ) {
                $this->catatLog(
                    $jenis,
                    $appointmentId,
                    $reminderId,
                    $nomor,
                    $pesan,
                    'berhasil',
                    json_encode($hasil)
                );

                return [
                    'status' => 'berhasil',
                    'wa_link' => null,
                ];
            }

            $keterangan = json_encode($hasil)
                ?: $response->body();

            $this->catatLog(
                $jenis,
                $appointmentId,
                $reminderId,
                $nomor,
                $pesan,
                'fallback',
                $keterangan
            );

            return [
                'status' => 'fallback',
                'wa_link' => $waLink,
            ];
        } catch (Throwable $e) {
            Log::warning('Pengiriman WhatsApp melalui Fonnte gagal.', [
                'nomor' => $nomor,
                'error' => $e->getMessage(),
            ]);

            $this->catatLog(
                $jenis,
                $appointmentId,
                $reminderId,
                $nomor,
                $pesan,
                'fallback',
                $e->getMessage()
            );

            return [
                'status' => 'fallback',
                'wa_link' => $waLink,
            ];
        }
    }

    public function buatPesanPengingat(Reminder $reminder): string
    {
        $reminder->loadMissing([
            'pet',
            'servisTerakhir',
        ]);

        $pet = $reminder->pet;
        $appointment = $reminder->servisTerakhir;

        $tanggalLayanan = $appointment->waktu_selesai_aktual
            ?? $appointment->mulai;

        $hariSejakGrooming = (int) $tanggalLayanan
            ->diffInDays(now());

        $bookingUrl = route('booking.create');

        return "Halo {$pet->customer->nama}, kami ingin mengingatkan "
            . "bahwa sudah {$hariSejakGrooming} hari sejak grooming "
            . "terakhir {$pet->nama}.\n\n"
            . "Yuk, jadwalkan grooming berikutnya melalui:\n"
            . "{$bookingUrl}\n\n"
            . "Terima kasih telah menggunakan layanan kami.";
    }

    public function buatLinkManual(string $nomor, string $pesan): string
    {
        return 'https://wa.me/'
            . $this->normalisasiNomor($nomor)
            . '?text=' . urlencode($pesan);
    }

    private function normalisasiNomor(string $nomor): string
    {
        $nomor = preg_replace('/\D/', '', $nomor);

        if (str_starts_with($nomor, '0')) {
            return '62' . substr($nomor, 1);
        }

        if (str_starts_with($nomor, '8')) {
            return '62' . $nomor;
        }

        return $nomor;
    }

    private function catatLog(
        string $jenis,
        ?int $appointmentId,
        ?int $reminderId,
        string $nomor,
        string $pesan,
        string $status,
        ?string $respons
    ): void {
        try {
            NotificationLog::create([
                'jenis' => $jenis,
                'appointment_id' => $appointmentId,
                'reminder_id' => $reminderId,
                'nomor_tujuan' => $nomor,
                'pesan' => $pesan,
                'status' => $status,
                'respons' => $respons,
                'terkirim_at' => $status === 'berhasil' ? now() : null,
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal mencatat log notifikasi WhatsApp.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}