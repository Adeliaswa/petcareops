<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\GroomingRecord;

class ReminderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'resepsionis', 'groomer'] as $role) {
            Role::create(['name' => $role]);
        }
    }

    private function buatUserDenganRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function buatReminder(string $status, string $namaPet): Reminder
    {
        $customer = Customer::create([
            'nama' => 'Pemilik ' . $namaPet,
            'no_wa' => '628' . random_int(1000000000, 9999999999),
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => $namaPet,
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $groomerUser = User::factory()->create();

        $groomer = Groomer::create([
            'user_id' => $groomerUser->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->subDays(28),
            'selesai_estimasi' => now()->subDays(28)->addHour(),
            'durasi_estimasi' => 60,
            'status' => 'selesai',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        GroomingRecord::create([
    'appointment_id' => $appointment->id,
    'durasi_aktual' => 45,
    'catatan_grooming' => 'Grooming selesai.',
]);

        return Reminder::create([
            'pet_id' => $pet->id,
            'servis_terakhir_id' => $appointment->id,
            'tanggal_jatuh_tempo' => today(),
            'status' => $status,
        ]);
    }

    public function test_admin_can_access_reminder_list(): void
    {
        $user = $this->buatUserDenganRole('admin');

        $this->actingAs($user)
            ->get('/staf/pengingat')
            ->assertOk();
    }

    public function test_resepsionis_can_access_reminder_list(): void
    {
        $user = $this->buatUserDenganRole('resepsionis');

        $this->actingAs($user)
            ->get('/staf/pengingat')
            ->assertOk();
    }

    public function test_groomer_cannot_access_reminder_list(): void
    {
        $user = $this->buatUserDenganRole('groomer');

        $this->actingAs($user)
            ->get('/staf/pengingat')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/staf/pengingat')
            ->assertRedirect(route('login'));
    }

    public function test_reminder_list_can_be_filtered_by_status(): void
    {
        $user = $this->buatUserDenganRole('admin');

        $this->buatReminder('menunggu', 'Milo');
        $this->buatReminder('terkirim', 'Luna');

        $this->actingAs($user)
            ->get('/staf/pengingat?status=menunggu')
            ->assertOk()
            ->assertSee('Milo')
            ->assertDontSee('Luna');
    }

    public function test_invalid_status_filter_is_rejected(): void
    {
        $user = $this->buatUserDenganRole('admin');

        $this->actingAs($user)
            ->get('/staf/pengingat?status=invalid')
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_run_reminder_generation_manually(): void
{
    $user = $this->buatUserDenganRole('admin');

    $this->buatReminder('menunggu', 'Milo');

    Reminder::query()->delete();

    $this->actingAs($user)
        ->post('/staf/pengingat/jalankan')
        ->assertRedirect(route('staf.pengingat.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reminders', [
        'status' => 'menunggu',
    ]);
}

public function test_pending_reminder_displays_manual_whatsapp_link(): void
{
    $user = $this->buatUserDenganRole('admin');

    $reminder = $this->buatReminder('menunggu', 'Milo');

    $this->actingAs($user)
        ->get('/staf/pengingat')
        ->assertOk()
        ->assertSee('Kirim WhatsApp Manual')
        ->assertSee('https://wa.me/628', false);
}

public function test_sent_reminder_does_not_display_manual_whatsapp_link(): void
{
    $user = $this->buatUserDenganRole('admin');

    $this->buatReminder('terkirim', 'Luna');

    $this->actingAs($user)
        ->get('/staf/pengingat')
        ->assertOk()
        ->assertDontSee('Kirim WhatsApp Manual');
}

}