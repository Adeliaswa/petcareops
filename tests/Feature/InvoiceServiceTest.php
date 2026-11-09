<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function buatAppointment(string $status = 'selesai'): array
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Tes',
            'no_wa' => '6281234567890',
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $user = User::factory()->create();

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->subHours(2),
            'selesai_estimasi' => now()->subHour(),
            'durasi_estimasi' => 60,
            'status' => $status,
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'walk-in',
        ]);

        $service = Service::create([
            'nama' => 'Full Grooming',
            'deskripsi' => 'Layanan grooming lengkap',
            'durasi_dasar' => 60,
            'harga' => 100000,
            'aktif' => true,
        ]);

        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'nama_layanan' => $service->nama,
            'durasi_dasar' => $service->durasi_dasar,
            'harga' => $service->harga,
        ]);

        return compact('appointment', 'service');
    }

    public function test_invoice_dibuat_dari_snapshot_harga_layanan(): void
    {
        ['appointment' => $appointment] = $this->buatAppointment();

        $invoice = app(InvoiceService::class)
            ->buatUntukAppointment($appointment);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'appointment_id' => $appointment->id,
            'total' => 100000,
        ]);

        $this->assertStringStartsWith('INV-', $invoice->nomor_invoice);
    }

    public function test_invoice_tidak_bisa_dibuat_sebelum_appointment_selesai(): void
    {
        ['appointment' => $appointment] = $this->buatAppointment('menunggu');

        $this->expectException(ValidationException::class);

        app(InvoiceService::class)->buatUntukAppointment($appointment);
    }

    public function test_pemanggilan_berulang_tidak_membuat_invoice_ganda(): void
    {
        ['appointment' => $appointment] = $this->buatAppointment();

        $service = app(InvoiceService::class);

        $invoicePertama = $service->buatUntukAppointment($appointment);
        $invoiceKedua = $service->buatUntukAppointment($appointment);

        $this->assertSame($invoicePertama->id, $invoiceKedua->id);
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_perubahan_harga_layanan_tidak_mengubah_total_invoice(): void
    {
        [
            'appointment' => $appointment,
            'service' => $serviceMaster,
        ] = $this->buatAppointment();

        $serviceMaster->update([
            'harga' => 200000,
        ]);

        $invoice = app(InvoiceService::class)
            ->buatUntukAppointment($appointment);

        $this->assertSame(100000, $invoice->total);
    }
}
