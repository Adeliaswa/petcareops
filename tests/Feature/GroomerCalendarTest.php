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

class GroomerCalendarTest extends TestCase
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


private function buatAppointment(
    Groomer $groomer,
    string $namaPet,
    string $mulai
): Appointment {
    $customer = Customer::create([
        'nama' => 'Pelanggan ' . $namaPet,
        'no_wa' => '628' . fake()->unique()->numerify('###########'),
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
        'status' => 'menunggu',
        'token' => bin2hex(random_bytes(32)),
        'rincian_estimasi' => [],
        'sumber' => 'publik',
    ]);
}


    public function test_admin_can_view_groomer_calendar(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $groomer] = $this->buatGroomer('Groomer Satu');

        $this->buatAppointment(
            $groomer,
            'Milo',
            '2026-11-10 09:00:00'
        );

        $this->actingAs($admin)
            ->get(route('staf.groomers.calendar'))
            ->assertOk()
            ->assertViewIs('staff.groomers.calendar')
            ->assertSee('Milo');
    }

    public function test_resepsionis_can_view_groomer_calendar(): void
    {
        $resepsionis = User::factory()->create();
        $resepsionis->assignRole('resepsionis');

        $this->actingAs($resepsionis)
            ->get(route('staf.groomers.calendar'))
            ->assertOk()
            ->assertViewIs('staff.groomers.calendar');
    }

    public function test_groomer_cannot_access_staff_calendar(): void
    {
        [$groomerUser] = $this->buatGroomer('Groomer Satu');

        $this->actingAs($groomerUser)
            ->get(route('staf.groomers.calendar'))
            ->assertForbidden();
    }

    public function test_calendar_can_be_filtered_by_date_and_groomer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $groomerSatu] = $this->buatGroomer('Groomer Satu');
        [, $groomerDua] = $this->buatGroomer('Groomer Dua');

        $this->buatAppointment(
            $groomerSatu,
            'Milo',
            '2026-11-10 09:00:00'
        );

        $this->buatAppointment(
            $groomerDua,
            'Luna',
            '2026-11-10 10:00:00'
        );

        $this->buatAppointment(
            $groomerSatu,
            'Bobby',
            '2026-11-20 09:00:00'
        );

        $response = $this->actingAs($admin)
            ->get(route('staf.groomers.calendar', [
                'tanggal_mulai' => '2026-11-10',
                'tanggal_akhir' => '2026-11-15',
                'groomer_id' => $groomerSatu->id,
            ]));

        $response->assertOk()
            ->assertSee('Milo')
            ->assertDontSee('Luna')
            ->assertDontSee('Bobby');
    }
}