<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\GroomingRecord;
use Carbon\Carbon;

class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function buatGroomer(string $nama): array
    {
        $user = User::factory()->create([
            'name' => $nama,
        ]);

        $user->assignRole('groomer');

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        return [$user, $groomer];
    }

    private function buatAppointment(Groomer $groomer): Appointment
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Pengujian',
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

        return Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->addHour(),
            'selesai_estimasi' => now()->addHours(2),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);
    }

    public function test_groomer_can_start_own_appointment(): void
    {
        [$user, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);

        $this->actingAs($user)
            ->post("/groomer/tugas/{$appointment->id}/mulai")
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'dikerjakan',
        ]);
    }

    public function test_groomer_cannot_start_another_groomers_appointment(): void
    {
        [$groomerSatu] = $this->buatGroomer('Groomer Satu');
        [, $groomerDua] = $this->buatGroomer('Groomer Dua');

        $appointment = $this->buatAppointment($groomerDua);

        $this->actingAs($groomerSatu)
            ->post("/groomer/tugas/{$appointment->id}/mulai")
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_groomer_cannot_start_appointment_that_is_not_waiting(): void
    {
        [$user, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);
        $appointment->update(['status' => 'dikerjakan']);

        $this->actingAs($user)
            ->post("/groomer/tugas/{$appointment->id}/mulai")
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'dikerjakan',
        ]);
    }

    public function test_resepsionis_can_cancel_waiting_appointment(): void
    {
        $resepsionis = User::factory()->create();
        $resepsionis->assignRole('resepsionis');

        [, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);

        $this->actingAs($resepsionis)
            ->post("/staf/appointments/{$appointment->id}/batal", [
                'alasan_batal' => 'Pelanggan membatalkan booking.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'dibatalkan',
        ]);
    }

    public function test_resepsionis_cannot_cancel_appointment_that_is_already_started(): void
    {
        $resepsionis = User::factory()->create();
        $resepsionis->assignRole('resepsionis');

        [, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);
        $appointment->update(['status' => 'dikerjakan']);

        $this->actingAs($resepsionis)
            ->post("/staf/appointments/{$appointment->id}/batal", [
                'alasan_batal' => 'Pelanggan membatalkan booking.',
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'dikerjakan',
        ]);
    }

    public function test_groomer_can_finish_own_appointment_and_save_grooming_record(): void
{
    [$user, $groomer] = $this->buatGroomer('Groomer Satu');
    $appointment = $this->buatAppointment($groomer);

    $appointment->update([
        'status' => 'dikerjakan',
        'waktu_mulai_aktual' => now()->subMinutes(10),
    ]);

    $this->actingAs($user)
        ->post("/groomer/tugas/{$appointment->id}/selesai", [
            'catatan_grooming' => 'Kondisi hewan baik setelah grooming.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'selesai',
    ]);

    $this->assertDatabaseHas('grooming_records', [
        'appointment_id' => $appointment->id,
        'durasi_aktual' => 10,
        'catatan_grooming' => 'Kondisi hewan baik setelah grooming.',
    ]);

    $this->assertNotNull(
        $appointment->fresh()->waktu_selesai_aktual
    );
}

public function test_groomer_cannot_finish_another_groomers_appointment(): void
{
    [$groomerSatu] = $this->buatGroomer('Groomer Satu');
    [$groomerDua, $profilGroomerDua] = $this->buatGroomer('Groomer Dua');

    $appointment = $this->buatAppointment($profilGroomerDua);

    $appointment->update([
        'status' => 'dikerjakan',
        'waktu_mulai_aktual' => now()->subMinutes(10),
    ]);

    $this->actingAs($groomerSatu)
        ->post("/groomer/tugas/{$appointment->id}/selesai", [
            'catatan_grooming' => 'Catatan pengujian.',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'dikerjakan',
    ]);

    $this->assertDatabaseMissing('grooming_records', [
        'appointment_id' => $appointment->id,
    ]);
}

public function test_groomer_cannot_finish_appointment_that_is_not_in_progress(): void
{
    [$user, $groomer] = $this->buatGroomer('Groomer Satu');
    $appointment = $this->buatAppointment($groomer);

    $this->actingAs($user)
        ->post("/groomer/tugas/{$appointment->id}/selesai", [
            'catatan_grooming' => 'Catatan pengujian.',
        ])
        ->assertSessionHasErrors();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'menunggu',
    ]);

    $this->assertDatabaseMissing('grooming_records', [
        'appointment_id' => $appointment->id,
    ]);
}

public function test_groomer_cannot_finish_appointment_twice(): void
{
    [$user, $groomer] = $this->buatGroomer('Groomer Satu');
    $appointment = $this->buatAppointment($groomer);

    $appointment->update([
        'status' => 'dikerjakan',
        'waktu_mulai_aktual' => now()->subMinutes(10),
    ]);

    GroomingRecord::create([
        'appointment_id' => $appointment->id,
        'durasi_aktual' => 10,
        'catatan_grooming' => 'Catatan pertama.',
    ]);

    $this->actingAs($user)
        ->post("/groomer/tugas/{$appointment->id}/selesai", [
            'catatan_grooming' => 'Catatan kedua.',
        ])
        ->assertSessionHasErrors();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'dikerjakan',
    ]);

    $this->assertDatabaseHas('grooming_records', [
        'appointment_id' => $appointment->id,
        'catatan_grooming' => 'Catatan pertama.',
    ]);

    $this->assertDatabaseMissing('grooming_records', [
        'appointment_id' => $appointment->id,
        'catatan_grooming' => 'Catatan kedua.',
    ]);
}

public function test_grooming_duration_over_600_minutes_is_rejected_atomically(): void
{
    [$user, $groomer] = $this->buatGroomer('Groomer Satu');
    $appointment = $this->buatAppointment($groomer);

    $appointment->update([
        'status' => 'dikerjakan',
        'waktu_mulai_aktual' => now()->subMinutes(601),
    ]);

    $this->actingAs($user)
        ->post("/groomer/tugas/{$appointment->id}/selesai", [
            'catatan_grooming' => 'Catatan pengujian durasi.',
        ])
        ->assertSessionHasErrors('durasi_aktual');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'dikerjakan',
        'waktu_selesai_aktual' => null,
    ]);

    $this->assertDatabaseMissing('grooming_records', [
        'appointment_id' => $appointment->id,
    ]);
}


public function test_groomer_can_finish_appointment_with_minimum_duration(): void
{
    Carbon::setTestNow('2026-11-09 10:00:00');

    try {
        [$user, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);

        $appointment->update([
            'status' => 'dikerjakan',
            'waktu_mulai_aktual' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->post("/groomer/tugas/{$appointment->id}/selesai", [
                'catatan_grooming' => 'Grooming selesai dalam satu menit.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grooming_records', [
            'appointment_id' => $appointment->id,
            'durasi_aktual' => 1,
        ]);
    } finally {
        Carbon::setTestNow();
    }
}

public function test_groomer_can_finish_appointment_with_maximum_duration(): void
{
    Carbon::setTestNow('2026-11-09 10:00:00');

    try {
        [$user, $groomer] = $this->buatGroomer('Groomer Satu');
        $appointment = $this->buatAppointment($groomer);

        $appointment->update([
            'status' => 'dikerjakan',
            'waktu_mulai_aktual' => now()->subMinutes(600),
        ]);

        $this->actingAs($user)
            ->post("/groomer/tugas/{$appointment->id}/selesai", [
                'catatan_grooming' => 'Grooming selesai dalam batas maksimal.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('grooming_records', [
            'appointment_id' => $appointment->id,
            'durasi_aktual' => 600,
        ]);
    } finally {
        Carbon::setTestNow();
    }
}

}