<?php

namespace Tests\Feature;

use App\Models\Groomer;
use App\Models\GroomerShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroomerShiftTest extends TestCase
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

    private function createShift(
        Groomer $groomer,
        int $day = 1,
        string $start = '08:00',
        string $end = '12:00'
    ): GroomerShift {
        return GroomerShift::create([
            'groomer_id' => $groomer->id,
            'hari' => $day,
            'jam_mulai' => $start,
            'jam_selesai' => $end,
        ]);
    }

    public function test_admin_can_view_groomer_shifts(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $shift = $this->createShift($groomer);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.groomer.shift.index', $groomer));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $shift->id,
                'hari' => 1,
            ]);
    }

    public function test_admin_can_create_groomer_shift(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.shift.store', $groomer), [
                'hari' => 1,
                'jam_mulai' => '08:00',
                'jam_selesai' => '12:00',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('groomer_shifts', [
            'groomer_id' => $groomer->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
        ]);
    }

    public function test_shift_end_time_must_be_after_start_time(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.shift.store', $groomer), [
                'hari' => 1,
                'jam_mulai' => '12:00',
                'jam_selesai' => '12:00',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('jam_selesai');
    }

    public function test_admin_cannot_create_overlapping_shift(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $this->createShift($groomer, 1, '08:00', '12:00');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.shift.store', $groomer), [
                'hari' => 1,
                'jam_mulai' => '10:00',
                'jam_selesai' => '14:00',
            ]);

        $response->assertUnprocessable();
    }

    public function test_admin_can_create_adjacent_shifts(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $this->createShift($groomer, 1, '08:00', '12:00');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.groomer.shift.store', $groomer), [
                'hari' => 1,
                'jam_mulai' => '12:00',
                'jam_selesai' => '16:00',
            ]);

        $response->assertCreated();
    }

    public function test_admin_can_update_groomer_shift(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $shift = $this->createShift($groomer);

        $response = $this->actingAs($admin)
            ->putJson(
                route('admin.groomer.shift.update', [$groomer, $shift]),
                [
                    'hari' => 2,
                    'jam_mulai' => '09:00',
                    'jam_selesai' => '13:00',
                ]
            );

        $response->assertOk()
            ->assertJsonPath('data.hari', 2);
    }

    public function test_admin_can_delete_groomer_shift(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();
        $shift = $this->createShift($groomer);

        $response = $this->actingAs($admin)
            ->deleteJson(
                route('admin.groomer.shift.destroy', [$groomer, $shift])
            );

        $response->assertOk();

        $this->assertDatabaseMissing('groomer_shifts', [
            'id' => $shift->id,
        ]);
    }

    public function test_receptionist_cannot_manage_groomer_shifts(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.groomer.shift.index', $groomer));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_manage_groomer_shifts(): void
    {
        $groomer = $this->createGroomer();

        $response = $this->actingAs($groomer->user)
            ->getJson(route('admin.groomer.shift.index', $groomer));

        $response->assertForbidden();
    }
}