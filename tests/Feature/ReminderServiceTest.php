<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\GroomingRecord;
use App\Models\Pet;
use App\Models\Reminder;
use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function buatPet(string $nama = 'Milo'): array
    {
        $customer = Customer::create([
            'nama' => 'Pemilik ' . $nama,
            'no_wa' => '628' . str_pad(
                (string) random_int(0, 9999999999),
                10,
                '0',
                STR_PAD_LEFT
            ),
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => $nama,
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

        return [$pet, $groomer];
    }

    private function buatRiwayatGrooming(
        Pet $pet,
        Groomer $groomer,
        int $hariLalu,
        string $status = 'selesai'
    ): Appointment {
        $waktuSelesai = now()->subDays($hariLalu);

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => $waktuSelesai->copy()->subHour(),
            'selesai_estimasi' => $waktuSelesai,
            'durasi_estimasi' => 60,
            'status' => $status,
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
            'waktu_mulai_aktual' => $waktuSelesai->copy()->subMinutes(45),
            'waktu_selesai_aktual' => $waktuSelesai,
        ]);

        GroomingRecord::create([
            'appointment_id' => $appointment->id,
            'durasi_aktual' => 45,
            'catatan_grooming' => 'Grooming selesai.',
        ]);

        return $appointment;
    }

    private function buatAppointmentAktif(
        Pet $pet,
        Groomer $groomer
    ): Appointment {
        return Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => today()->addDay()->setTime(10, 0),
            'selesai_estimasi' => today()->addDay()->setTime(11, 0),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-09 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_due_grooming_creates_one_reminder(): void
    {
        [$pet, $groomer] = $this->buatPet();

        $this->buatRiwayatGrooming($pet, $groomer, 28);

        $jumlah = app(ReminderService::class)
            ->generateDueReminders();

        $this->assertSame(1, $jumlah);

        $this->assertDatabaseHas('reminders', [
            'pet_id' => $pet->id,
            'status' => 'menunggu',
            'tanggal_jatuh_tempo' => '2026-11-09',
        ]);
    }

    public function test_grooming_that_is_not_due_does_not_create_reminder(): void
    {
        [$pet, $groomer] = $this->buatPet();

        $this->buatRiwayatGrooming($pet, $groomer, 10);

        $jumlah = app(ReminderService::class)
            ->generateDueReminders();

        $this->assertSame(0, $jumlah);
        $this->assertDatabaseCount('reminders', 0);
    }

    public function test_running_service_twice_does_not_duplicate_reminder(): void
    {
        [$pet, $groomer] = $this->buatPet();

        $this->buatRiwayatGrooming($pet, $groomer, 28);

        $service = app(ReminderService::class);

        $this->assertSame(1, $service->generateDueReminders());
        $this->assertSame(0, $service->generateDueReminders());

        $this->assertDatabaseCount('reminders', 1);
    }

    public function test_future_active_appointment_closes_pending_reminder(): void
    {
        [$pet, $groomer] = $this->buatPet();

        $appointment = $this->buatRiwayatGrooming(
            $pet,
            $groomer,
            35
        );

        $reminder = Reminder::create([
            'pet_id' => $pet->id,
            'servis_terakhir_id' => $appointment->id,
            'tanggal_jatuh_tempo' => '2026-11-02',
            'status' => 'menunggu',
        ]);

        $this->buatAppointmentAktif($pet, $groomer);

        $jumlah = app(ReminderService::class)
            ->generateDueReminders();

        $this->assertSame(0, $jumlah);

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'status' => 'ditutup',
        ]);
    }

    public function test_pet_without_completed_grooming_does_not_get_reminder(): void
    {
        [$pet, $groomer] = $this->buatPet();

        Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->subDays(40),
            'selesai_estimasi' => now()->subDays(40)->addHour(),
            'durasi_estimasi' => 60,
            'status' => 'selesai',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        $jumlah = app(ReminderService::class)
            ->generateDueReminders();

        $this->assertSame(0, $jumlah);
        $this->assertDatabaseCount('reminders', 0);
    }

    public function test_custom_reminder_interval_is_used(): void
    {
        [$pet, $groomer] = $this->buatPet();

        AppSetting::create([
            'key' => 'jarak_pengingat_hari',
            'value' => '14',
        ]);

        $this->buatRiwayatGrooming($pet, $groomer, 14);

        $jumlah = app(ReminderService::class)
            ->generateDueReminders();

        $this->assertSame(1, $jumlah);

        $this->assertDatabaseHas('reminders', [
            'pet_id' => $pet->id,
            'tanggal_jatuh_tempo' => '2026-11-09',
        ]);
    }
}
