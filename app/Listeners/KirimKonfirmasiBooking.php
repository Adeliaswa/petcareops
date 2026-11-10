<?php

namespace App\Listeners;

use App\Events\BookingDibuat;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Throwable;

class KirimKonfirmasiBooking
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    public function handle(BookingDibuat $event): void
    {
        $appointment = $event->appointment;

        try {
            $appointment->loadMissing([
                'pet.customer',
                'services',
            ]);

            // Pastikan konfirmasi tidak dikirim dua kali.
            $sudahDikirim = NotificationLog::query()
                ->where('jenis', 'konfirmasi_booking')
                ->where('appointment_id', $appointment->id)
                ->exists();

            if ($sudahDikirim) {
                return;
            }

            $pet = $appointment->pet;
            $customer = $pet?->customer;

            if (!$customer || !$customer->no_wa) {
                Log::warning(
                    'Konfirmasi booking tidak dikirim karena nomor WhatsApp tidak tersedia.',
                    ['appointment_id' => $appointment->id]
                );

                return;
            }

            $namaLayanan = $appointment->services
                ->pluck('nama_layanan')
                ->implode(', ');

            $tanggal = $appointment->mulai
                ->timezone('Asia/Jakarta')
                ->format('d-m-Y H:i');

            $statusUrl = route('booking.status', [
                'token' => $appointment->token,
            ]);

            $pesan = "Halo {$customer->nama}, booking grooming berhasil dibuat.\n\n"
                . "Nama pet: {$pet->nama}\n"
                . "Layanan: {$namaLayanan}\n"
                . "Jadwal: {$tanggal} WIB\n"
                . "Status: {$appointment->status}\n\n"
                . "Cek status booking:\n{$statusUrl}\n\n"
                . "Terima kasih telah menggunakan layanan kami.";

            $this->whatsAppService->kirim(
                'konfirmasi_booking',
                $customer->no_wa,
                $pesan,
                $appointment->id
            );
        } catch (Throwable $e) {
            Log::error('Gagal memproses konfirmasi booking WhatsApp.', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
