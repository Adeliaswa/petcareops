<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pet;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function buatInvoice(string $status = 'selesai'): Invoice
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

        $userGroomer = User::factory()->create();

        $groomer = Groomer::create([
            'user_id' => $userGroomer->id,
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

        return Invoice::create([
            'appointment_id' => $appointment->id,
            'nomor_invoice' => 'INV-TEST-' . $appointment->id,
            'total' => 100000,
            'diterbitkan_pada' => now(),
        ]);
    }

    public function test_pembayaran_sebagian_dapat_dicatat(): void
    {
        $invoice = $this->buatInvoice();
        $resepsionis = User::factory()->create();

        $payment = app(PaymentService::class)->catat(
            $invoice,
            [
                'metode' => 'tunai',
                'nominal' => 40000,
            ],
            $resepsionis
        );

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'invoice_id' => $invoice->id,
            'nominal' => 40000,
            'metode' => 'tunai',
            'dicatat_oleh' => $resepsionis->id,
        ]);

        $invoice->refresh();

        $this->assertSame(40000, $invoice->total_dibayar);
        $this->assertSame(60000, $invoice->sisa_tagihan);
        $this->assertSame('sebagian', $invoice->status_pembayaran);
    }

    public function test_pembayaran_lunas_mengubah_status_invoice(): void
    {
        $invoice = $this->buatInvoice();
        $resepsionis = User::factory()->create();

        $service = app(PaymentService::class);

        $service->catat($invoice, [
            'metode' => 'transfer',
            'nominal' => 40000,
        ], $resepsionis);

        $service->catat($invoice, [
            'metode' => 'qris',
            'nominal' => 60000,
        ], $resepsionis);

        $invoice->refresh();

        $this->assertSame(100000, $invoice->total_dibayar);
        $this->assertSame(0, $invoice->sisa_tagihan);
        $this->assertSame('lunas', $invoice->status_pembayaran);
    }

    public function test_pembayaran_melebihi_sisa_tagihan_ditolak(): void
    {
        $invoice = $this->buatInvoice();
        $resepsionis = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->catat($invoice, [
            'metode' => 'tunai',
            'nominal' => 100001,
        ], $resepsionis);
    }

    public function test_pembayaran_hanya_bisa_dicatat_setelah_appointment_selesai(): void
    {
        $invoice = $this->buatInvoice('menunggu');
        $resepsionis = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->catat($invoice, [
            'metode' => 'tunai',
            'nominal' => 50000,
        ], $resepsionis);
    }

    public function test_admin_dapat_menghapus_pembayaran_dengan_soft_delete(): void
    {
        $invoice = $this->buatInvoice();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payment = app(PaymentService::class)->catat($invoice, [
            'metode' => 'tunai',
            'nominal' => 40000,
        ], $admin);

        app(PaymentService::class)->hapus($payment, $admin);

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);

        $invoice->refresh();

        $this->assertSame(0, $invoice->total_dibayar);
        $this->assertSame('belum_bayar', $invoice->status_pembayaran);
    }

    public function test_non_admin_tidak_dapat_menghapus_pembayaran(): void
    {
        $invoice = $this->buatInvoice();
        $resepsionis = User::factory()->create();

        $payment = app(PaymentService::class)->catat($invoice, [
            'metode' => 'tunai',
            'nominal' => 40000,
        ], $resepsionis);

        $this->expectException(AuthorizationException::class);

        app(PaymentService::class)->hapus($payment, $resepsionis);
    }

    public function test_metode_pembayaran_tidak_valid_ditolak(): void
    {
        $invoice = $this->buatInvoice();
        $resepsionis = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->catat($invoice, [
            'metode' => 'kartu_kredit',
            'nominal' => 50000,
        ], $resepsionis);
    }
}
