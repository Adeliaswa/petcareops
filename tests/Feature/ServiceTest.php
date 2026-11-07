<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceTest extends TestCase
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

    public function test_admin_can_create_service(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.layanan.store'), [
                'nama' => 'Grooming Kucing',
                'deskripsi' => 'Layanan grooming untuk kucing.',
                'durasi_dasar' => 60,
                'harga' => 75000,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.nama', 'Grooming Kucing')
            ->assertJsonPath('data.aktif', true);

        $this->assertDatabaseHas('services', [
            'nama' => 'Grooming Kucing',
            'durasi_dasar' => 60,
            'harga' => 75000,
            'aktif' => true,
        ]);
    }

    public function test_service_duration_must_be_greater_than_zero(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.layanan.store'), [
                'nama' => 'Grooming Kucing',
                'durasi_dasar' => 0,
                'harga' => 75000,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('durasi_dasar');
    }

    public function test_service_price_cannot_be_negative(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.layanan.store'), [
                'nama' => 'Grooming Kucing',
                'durasi_dasar' => 60,
                'harga' => -10000,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('harga');
    }

    public function test_admin_can_update_service(): void
    {
        $admin = $this->createUserWithRole('admin');

        $service = Service::create([
            'nama' => 'Grooming Kucing',
            'durasi_dasar' => 60,
            'harga' => 75000,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)
            ->putJson(route('admin.layanan.update', $service), [
                'nama' => 'Grooming Kucing Premium',
                'durasi_dasar' => 90,
                'harga' => 100000,
                'aktif' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.nama', 'Grooming Kucing Premium')
            ->assertJsonPath('data.durasi_dasar', 90);
    }

    public function test_admin_cannot_delete_active_service(): void
    {
        $admin = $this->createUserWithRole('admin');

        $service = Service::create([
            'nama' => 'Grooming Kucing',
            'durasi_dasar' => 60,
            'harga' => 75000,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('admin.layanan.destroy', $service));

        $response->assertUnprocessable();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'aktif' => true,
        ]);
    }

    public function test_admin_can_delete_inactive_service(): void
    {
        $admin = $this->createUserWithRole('admin');

        $service = Service::create([
            'nama' => 'Grooming Kucing',
            'durasi_dasar' => 60,
            'harga' => 75000,
            'aktif' => false,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('admin.layanan.destroy', $service));

        $response->assertOk();

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }

    public function test_receptionist_cannot_access_service_management(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.layanan.index'));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_access_service_management(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $response = $this->actingAs($groomer)
            ->getJson(route('admin.layanan.index'));

        $response->assertForbidden();
    }
}