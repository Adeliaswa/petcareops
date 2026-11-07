<?php

namespace Tests\Feature;

use App\Models\Groomer;
use App\Models\GroomerTimeOff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroomerTimeOffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'resepsionis', 'groomer'] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createGroomer(): Groomer
    {
        $user = $this->createUserWithRole('groomer');

        return Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'dasar',
            'bisa_kucing' => false,
            'bisa_agresif' => false,
        ]);
    }

    private function createTimeOff(Groomer $groomer): GroomerTimeOff
    {
        return GroomerTimeOff::create([
            'groomer_id' => $groomer->id,
            'tanggal_mulai' => '2026-11-10',
            'tanggal_selesai' => '2026-11-12',
            'alasan' => 'Keperluan pribadi',
        ]);
    }

    public function test_admin_can_view_groomer_time_offs(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $timeOff = $this->createTimeOff($groomer);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.groomer.time-off.index', $groomer));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $timeOff->id,
                'alasan' => 'Keperluan pribadi',
            ]);
    }

    public function test_admin_can_create_groomer_time_off(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.time-off.store', $groomer), [
                'tanggal_mulai' => '2026-11-15',
                'tanggal_selesai' => '2026-11-17',
                'alasan' => 'Cuti keluarga',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('groomer_time_off', [
            'groomer_id' => $groomer->id,
            'alasan' => 'Cuti keluarga',
        ]);

        $timeOff = GroomerTimeOff::where('groomer_id', $groomer->id)
            ->first();

        $this->assertEquals('2026-11-15', $timeOff->tanggal_mulai->format('Y-m-d'));
        $this->assertEquals('2026-11-17', $timeOff->tanggal_selesai->format('Y-m-d'));
    }

    public function test_time_off_end_date_cannot_be_before_start_date(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.time-off.store', $groomer), [
                'tanggal_mulai' => '2026-11-17',
                'tanggal_selesai' => '2026-11-15',
                'alasan' => 'Cuti',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('tanggal_selesai');
    }

    public function test_admin_can_update_groomer_time_off(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $timeOff = $this->createTimeOff($groomer);

        $response = $this->actingAs($admin)
            ->putJson(
                route('admin.groomer.time-off.update', [$groomer, $timeOff]),
                [
                    'tanggal_mulai' => '2026-11-20',
                    'tanggal_selesai' => '2026-11-22',
                    'alasan' => 'Cuti tahunan',
                ]
            );

        $response->assertOk()
            ->assertJsonPath('data.alasan', 'Cuti tahunan');
    }

    public function test_admin_can_delete_groomer_time_off(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $timeOff = $this->createTimeOff($groomer);

        $response = $this->actingAs($admin)
            ->deleteJson(
                route('admin.groomer.time-off.destroy', [$groomer, $timeOff])
            );

        $response->assertOk();

        $this->assertDatabaseMissing('groomer_time_off', [
            'id' => $timeOff->id,
        ]);
    }

    public function test_admin_cannot_update_another_groomers_time_off(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomerA = $this->createGroomer();
        $groomerB = $this->createGroomer();
        $timeOff = $this->createTimeOff($groomerB);

        $response = $this->actingAs($admin)
            ->putJson(
                route('admin.groomer.time-off.update', [$groomerA, $timeOff]),
                [
                    'tanggal_mulai' => '2026-11-20',
                    'tanggal_selesai' => '2026-11-22',
                    'alasan' => 'Perubahan cuti',
                ]
            );

        $response->assertNotFound();
    }

    public function test_receptionist_cannot_manage_groomer_time_offs(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.groomer.time-off.index', $groomer));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_manage_groomer_time_offs(): void
    {
        $groomer = $this->createGroomer();

        $response = $this->actingAs($groomer->user)
            ->getJson(route('admin.groomer.time-off.index', $groomer));

        $response->assertForbidden();
    }


    public function test_admin_can_view_all_groomer_time_offs(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $timeOff = $this->createTimeOff($groomer);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.libur.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $timeOff->id,
                'alasan' => 'Keperluan pribadi',
            ]);
    }

    public function test_receptionist_cannot_access_admin_time_off_page(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.libur.index'));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_access_admin_time_off_page(): void
    {
        $groomer = $this->createGroomer();

        $response = $this->actingAs($groomer->user)
            ->getJson(route('admin.libur.index'));

        $response->assertForbidden();
    }

}