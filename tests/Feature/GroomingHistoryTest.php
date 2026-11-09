<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\GroomingRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroomingHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function buatGroomer(): array
    {
        $user = User::factory()->create();
        $user->assignRole('groomer');

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        return [$user, $groomer];
    }

    private function buatPet(string $nama): Pet
    {
        $customer = Customer::create([
            'nama' => 'Pemilik ' . $nama,
            'no_wa' => '628' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT),
        ]);

        return Pet::create([
            'customer_id' => $customer->id,
            'nama' => $nama,
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);
    }

    private function buatRiwayatGrooming(
        Pet $pet,
        Groomer $groomer,
        string $catatan,
        int $urutan = 0
    ): Appointment {
        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->subDays($urutan),
            'selesai_estimasi' => now()->subDays($urutan)->addHour(),
            'durasi_estimasi' => 60,
            'status' => 'selesai',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        GroomingRecord::create([
            'appointment_id' => $appointment->id,
            'durasi_aktual' => 45,
            'catatan_grooming' => $catatan,
        ]);

        return $appointment;
    }

    public function test_admin_can_view_pet_grooming_history(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $groomer] = $this->buatGroomer();
        $pet = $this->buatPet('Milo');

        $this->buatRiwayatGrooming(
            $pet,
            $groomer,
            'Kondisi bulu Milo baik.'
        );

        $this->actingAs($admin)
            ->get(route('staf.pets.grooming-history', $pet))
            ->assertOk()
            ->assertSee('Milo')
            ->assertSee('Kondisi bulu Milo baik.');
    }

    public function test_resepsionis_can_view_pet_grooming_history(): void
    {
        $resepsionis = User::factory()->create();
        $resepsionis->assignRole('resepsionis');

        [, $groomer] = $this->buatGroomer();
        $pet = $this->buatPet('Milo');

        $this->buatRiwayatGrooming(
            $pet,
            $groomer,
            'Grooming oleh resepsionis.'
        );

        $this->actingAs($resepsionis)
            ->get(route('staf.pets.grooming-history', $pet))
            ->assertOk()
            ->assertSee('Grooming oleh resepsionis.');
    }

    public function test_groomer_cannot_view_staff_pet_grooming_history(): void
    {
        [$groomerUser] = $this->buatGroomer();
        $pet = $this->buatPet('Milo');

        $this->actingAs($groomerUser)
            ->get(route('staf.pets.grooming-history', $pet))
            ->assertForbidden();
    }

    public function test_grooming_history_only_shows_records_for_selected_pet(): void
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    [, $groomer] = $this->buatGroomer();

    $petMilo = $this->buatPet('Milo');
    $petLuna = $this->buatPet('Luna');

    $this->buatRiwayatGrooming(
        $petMilo,
        $groomer,
        'Catatan khusus Milo.'
    );

    $this->buatRiwayatGrooming(
        $petLuna,
        $groomer,
        'Catatan khusus Luna.',
        1
    );

    $response = $this->actingAs($admin)
        ->get(route('staf.pets.grooming-history', $petMilo));

    $response->assertOk()
        ->assertSee('Catatan khusus Milo.')
        ->assertDontSee('Catatan khusus Luna.');
}

    public function test_grooming_history_does_not_show_appointments_without_grooming_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $groomer] = $this->buatGroomer();
        $pet = $this->buatPet('Milo');

        $this->buatRiwayatGrooming(
            $pet,
            $groomer,
            'Catatan grooming yang tersimpan.'
        );

        Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->addDay(),
            'selesai_estimasi' => now()->addDay()->addHour(),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        $this->actingAs($admin)
            ->get(route('staf.pets.grooming-history', $pet))
            ->assertOk()
            ->assertSee('Catatan grooming yang tersimpan.')
            ->assertViewHas('groomingRecords', function ($records) {
                return $records->total() === 1;
            });
    }

    public function test_grooming_history_is_paginated_by_ten_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $groomer] = $this->buatGroomer();
        $pet = $this->buatPet('Milo');

        for ($i = 1; $i <= 11; $i++) {
            $this->buatRiwayatGrooming(
                $pet,
                $groomer,
                'Catatan grooming ' . $i,
                $i
            );
        }

        $response = $this->actingAs($admin)
            ->get(route('staf.pets.grooming-history', $pet));

        $response->assertOk()
            ->assertViewHas('groomingRecords', function ($records) {
                return $records->count() === 10
                    && $records->total() === 11;
            });

        $this->actingAs($admin)
            ->get(route('staf.pets.grooming-history', [
                'pet' => $pet->id,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertViewHas('groomingRecords', function ($records) {
                return $records->count() === 1
                    && $records->total() === 11;
            });
    }
}
