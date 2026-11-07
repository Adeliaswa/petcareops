<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_access_admin_and_staff_pages(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/staf/appointments')
            ->assertOk();
    }

    public function test_admin_cannot_access_groomer_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get('/groomer/tugas')
            ->assertForbidden();
    }

    public function test_resepsionis_can_access_staff_page(): void
    {
        $resepsionis = $this->createUserWithRole('resepsionis');

        $this->actingAs($resepsionis)
            ->get('/staf/appointments')
            ->assertOk();
    }

    public function test_resepsionis_cannot_access_admin_or_groomer_pages(): void
    {
        $resepsionis = $this->createUserWithRole('resepsionis');

        $this->actingAs($resepsionis)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($resepsionis)
            ->get('/groomer/tugas')
            ->assertForbidden();
    }

    public function test_groomer_can_access_own_task_page(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $this->actingAs($groomer)
            ->get('/groomer/tugas')
            ->assertOk();
    }

    public function test_groomer_cannot_access_admin_or_staff_pages(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $this->actingAs($groomer)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($groomer)
            ->get('/staf/appointments')
            ->assertForbidden();
    }
}