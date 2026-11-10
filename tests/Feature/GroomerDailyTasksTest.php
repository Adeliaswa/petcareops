<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroomerDailyTasksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-09 08:00:00');

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function buatGroomer(string $nama): array
    {
        $user = User::factory()->create(['name' => $nama]);
        $user->assignRole('groomer');

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        return [$user, $groomer];
    }

    private function buatAppointment(
        Groomer $groomer,
        string $namaPet,
        string $mulai,
        string $status = 'menunggu'
    ): Appointment {
        $customer = Customer::create([
            'nama' => 'Pemilik ' . $namaPet,
            'no_wa' => '62812' . random_int(10000000, 99999999),
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

        $waktuMulai = Carbon::parse($mulai);

        return Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => $waktuMulai,
            'selesai_estimasi' => $waktuMulai->copy()->addMinutes(60),
            'durasi_estimasi' => 60,
            'status' => $status,
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);
    }

    public function test_groomer_only_sees_own_tasks_for_today(): void
    {
        [$userSatu, $groomerSatu] = $this->buatGroomer('Groomer Satu');
        [, $groomerDua] = $this->buatGroomer('Groomer Dua');

        $this->buatAppointment(
            $groomerSatu,
            'Milo',
            '2026-11-09 09:00:00'
        );

        $this->buatAppointment(
            $groomerSatu,
            'Luna',
            '2026-11-10 09:00:00'
        );

        $this->buatAppointment(
            $groomerDua,
            'Bobby',
            '2026-11-09 10:00:00'
        );

        $this->buatAppointment(
            $groomerSatu,
            'Coco',
            '2026-11-09 11:00:00',
            'dibatalkan'
        );

        $this->actingAs($userSatu)
            ->get(route('groomer.tasks.index'))
            ->assertOk()
            ->assertViewIs('groomer.tasks.index')
            ->assertSee('Milo')
            ->assertDontSee('Luna')
            ->assertDontSee('Bobby')
            ->assertDontSee('Coco');
    }

    public function test_groomer_without_groomer_profile_sees_no_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('groomer');

        $this->actingAs($user)
            ->get(route('groomer.tasks.index'))
            ->assertOk()
            ->assertViewIs('groomer.tasks.index')
            ->assertSee('Belum ada tugas grooming.');
    }

    public function test_non_groomer_cannot_access_daily_tasks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('groomer.tasks.index'))
            ->assertForbidden();
    }
}