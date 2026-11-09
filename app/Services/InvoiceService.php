<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function buatUntukAppointment(Appointment $appointment): Invoice
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = Appointment::query()
                ->with('services')
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            if (! in_array($appointment->status, ['selesai', 'dijemput'], true)) {
                throw ValidationException::withMessages([
                    'appointment' => 'Invoice hanya dapat dibuat untuk appointment yang sudah selesai atau dijemput.',
                ]);
            }

            $invoice = Invoice::where('appointment_id', $appointment->id)
                ->first();

            if ($invoice) {
                return $invoice;
            }

            $total = (int) $appointment->services->sum(
                fn ($service) => (float) $service->harga
            );

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'appointment' => 'Total invoice harus lebih dari Rp0.',
                ]);
            }

            return Invoice::create([
                'appointment_id' => $appointment->id,
                'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . str_pad(
                    (string) $appointment->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
                'total' => $total,
                'diterbitkan_pada' => now(),
            ]);
        });
    }
}