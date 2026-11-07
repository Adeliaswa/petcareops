<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'groomer']);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_view_staff_list(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/staff');

        $response->assertOk();
    }

    public function test_admin_can_create_staff_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'Staf Uji',
            'email' => 'staf.uji@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'resepsionis',
        ]);

        $response->assertRedirect(route('admin.staff.index'));

        $user = User::where('email', 'staf.uji@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('resepsionis'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_admin_can_deactivate_staff_account(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();
        $staff->assignRole('resepsionis');

        $response = $this->actingAs($admin)
            ->patch("/admin/staff/{$staff->id}/deactivate");

        $response->assertRedirect(route('admin.staff.index'));

        $this->assertFalse($staff->fresh()->is_active);
    }

    public function test_admin_can_activate_inactive_staff_account(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create([
            'is_active' => false,
        ]);
        $staff->assignRole('resepsionis');

        $response = $this->actingAs($admin)
            ->patch("/admin/staff/{$staff->id}/activate");

        $response->assertRedirect(route('admin.staff.index'));

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_admin_can_reset_staff_password(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();
        $staff->assignRole('resepsionis');

        $response = $this->actingAs($admin)
            ->put("/admin/staff/{$staff->id}/reset-password");

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = session('temporary_password');

        $this->assertNotEmpty($temporaryPassword);
        $this->assertTrue(
            Hash::check($temporaryPassword, $staff->fresh()->password)
        );
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from('/admin/staff')
            ->patch("/admin/staff/{$admin->id}/deactivate");

        $response->assertSessionHasErrors('account');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_service_prevents_deactivating_the_last_active_admin(): void
    {
        $lastAdmin = $this->createAdmin();

        // Simulasi pemanggil service selain admin terakhir.
        $otherUser = User::factory()->create();
        $otherUser->assignRole('resepsionis');

        try {
            app(AccountService::class)->nonaktifkan($lastAdmin, $otherUser);
            $this->fail('Seharusnya service menolak penonaktifan admin terakhir.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('account', $exception->errors());
        }

        $this->assertTrue($lastAdmin->fresh()->is_active);
    }
}