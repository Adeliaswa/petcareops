<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\AppointmentService;
use App\Models\Service;
class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;


private function buatAppointmentDenganLayanan(string $status = 'selesai'): Appointment
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
        'harga' => 100000,
    ]);

    return $appointment;
}


public function test_resepsionis_dapat_membuat_invoice_dari_appointment_selesai(): void
{
    $resepsionis = $this->buatPengguna('resepsionis');
    $appointment = $this->buatAppointmentDenganLayanan();

    $response = $this->actingAs($resepsionis)
        ->post(route('staf.appointments.invoice.store', $appointment));

    $invoice = Invoice::where('appointment_id', $appointment->id)->first();

    $response->assertRedirect(route('staf.invoices.show', $invoice));

    $this->assertNotNull($invoice);
    $this->assertSame(100000, $invoice->total);
}

public function test_invoice_tidak_dapat_dibuat_dari_appointment_yang_belum_selesai(): void
{
    $resepsionis = $this->buatPengguna('resepsionis');
    $appointment = $this->buatAppointmentDenganLayanan('menunggu');

    $response = $this->actingAs($resepsionis)
        ->post(route('staf.appointments.invoice.store', $appointment));

    $response->assertSessionHasErrors('appointment');

    $this->assertDatabaseMissing('invoices', [
        'appointment_id' => $appointment->id,
    ]);
}

public function test_pembuatan_invoice_berulang_tidak_membuat_invoice_ganda(): void
{
    $resepsionis = $this->buatPengguna('resepsionis');
    $appointment = $this->buatAppointmentDenganLayanan();

    $route = route('staf.appointments.invoice.store', $appointment);

    $this->actingAs($resepsionis)->post($route);
    $this->actingAs($resepsionis)->post($route);

    $this->assertDatabaseCount('invoices', 1);
}

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function buatPengguna(string $role): User
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function buatInvoice(): Invoice
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
            'status' => 'selesai',
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

    public function test_resepsionis_dapat_melihat_invoice(): void
    {
        $user = $this->buatPengguna('resepsionis');
        $invoice = $this->buatInvoice();

        $response = $this->actingAs($user)
            ->get(route('staf.invoices.show', $invoice));

        $response->assertOk();
        $response->assertSee($invoice->nomor_invoice);
        $response->assertSee('Pelanggan Tes');
        $response->assertSee('Milo');
    }

    public function test_admin_dapat_melihat_halaman_cetak_invoice(): void
    {
        $admin = $this->buatPengguna('admin');
        $invoice = $this->buatInvoice();

        $response = $this->actingAs($admin)
            ->get(route('staf.invoices.print', $invoice));

        $response->assertOk();
        $response->assertSee($invoice->nomor_invoice);
        $response->assertSee('Cetak Invoice');
    }

    public function test_resepsionis_dapat_mencatat_pembayaran_melalui_controller(): void
    {
        $resepsionis = $this->buatPengguna('resepsionis');
        $invoice = $this->buatInvoice();

        $response = $this->actingAs($resepsionis)
            ->from(route('staf.invoices.show', $invoice))
            ->post(route('staf.invoices.payments.store', $invoice), [
                'metode' => 'tunai',
                'nominal' => 40000,
                'dibayar_pada' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('staf.invoices.show', $invoice));

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'metode' => 'tunai',
            'nominal' => 40000,
            'dicatat_oleh' => $resepsionis->id,
        ]);
    }

    public function test_groomer_tidak_dapat_mengakses_invoice(): void
    {
        $groomer = $this->buatPengguna('groomer');
        $invoice = $this->buatInvoice();

        $response = $this->actingAs($groomer)
            ->get(route('staf.invoices.show', $invoice));

        $response->assertForbidden();
    }

    public function test_admin_dapat_menghapus_pembayaran_melalui_controller(): void
    {
        $admin = $this->buatPengguna('admin');
        $invoice = $this->buatInvoice();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'metode' => 'tunai',
            'nominal' => 40000,
            'dibayar_pada' => now(),
            'dicatat_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('staf.payments.destroy', $payment));

        $response->assertRedirect();

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);
    }

    public function test_resepsionis_tidak_dapat_menghapus_pembayaran(): void
    {
        $resepsionis = $this->buatPengguna('resepsionis');
        $invoice = $this->buatInvoice();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'metode' => 'tunai',
            'nominal' => 40000,
            'dibayar_pada' => now(),
            'dicatat_oleh' => $resepsionis->id,
        ]);

        $response = $this->actingAs($resepsionis)
            ->delete(route('staf.payments.destroy', $payment));

        $response->assertForbidden();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'deleted_at' => null,
        ]);
    }
}
