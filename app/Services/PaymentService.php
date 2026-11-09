<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    private const METODE_VALID = [
        'tunai',
        'transfer',
        'qris',
    ];

    public function catat(
        Invoice $invoice,
        array $data,
        User $pencatat
    ): Payment {
        if (! in_array($data['metode'] ?? null, self::METODE_VALID, true)) {
            throw ValidationException::withMessages([
                'metode' => 'Metode pembayaran tidak valid.',
            ]);
        }

        $nominal = $data['nominal'] ?? null;

        if (
            ! is_numeric($nominal) ||
            (int) $nominal != $nominal ||
            (int) $nominal <= 0
        ) {
            throw ValidationException::withMessages([
                'nominal' => 'Nominal pembayaran harus berupa bilangan bulat lebih dari Rp0.',
            ]);
        }

        return DB::transaction(function () use (
            $invoice,
            $data,
            $pencatat,
            $nominal
        ) {
            $invoiceTerkunci = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            $appointment = $invoiceTerkunci->appointment;

            if (! in_array($appointment->status, ['selesai', 'dijemput'], true)) {
                throw ValidationException::withMessages([
                    'invoice' => 'Pembayaran hanya dapat dicatat untuk appointment yang sudah selesai atau dijemput.',
                ]);
            }

            $totalDibayar = (int) $invoiceTerkunci
                ->payments()
                ->sum('nominal');

            $sisaTagihan = $invoiceTerkunci->total - $totalDibayar;

            if ((int) $nominal > $sisaTagihan) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal pembayaran melebihi sisa tagihan.',
                ]);
            }

            return $invoiceTerkunci->payments()->create([
                'metode' => $data['metode'],
                'nominal' => (int) $nominal,
                'dibayar_pada' => $data['dibayar_pada'] ?? now(),
                'dicatat_oleh' => $pencatat->id,
            ]);
        });
    }

    public function hapus(Payment $payment, User $pengguna): void
    {
        if (! $pengguna->hasRole('admin')) {
            throw new AuthorizationException(
                'Hanya admin yang dapat menghapus catatan pembayaran.'
            );
        }

        DB::transaction(function () use ($payment) {
            $invoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($payment->invoice_id);

            $paymentTerkunci = $invoice->payments()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $paymentTerkunci->delete();
        });
    }
}
