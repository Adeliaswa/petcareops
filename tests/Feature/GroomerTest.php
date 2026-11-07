<?php

namespace Tests\Feature;

use App\Models\Groomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroomerTest extends TestCase
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

    public function test_admin_can_view_groomer_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.groomer.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $groomer->id,
                'level_skill' => 'dasar',
            ]);
    }

    public function test_admin_can_view_groomer_detail(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.groomer.show', $groomer));

        $response->assertOk()
            ->assertJsonPath('data.id', $groomer->id)
            ->assertJsonPath('data.user.id', $groomer->user_id);
    }

    public function test_admin_can_update_groomer_capabilities(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->putJson(route('admin.groomer.update', $groomer), [
                'level_skill' => 'intermediate',
                'bisa_kucing' => true,
                'bisa_agresif' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.level_skill', 'intermediate')
            ->assertJsonPath('data.bisa_kucing', true)
            ->assertJsonPath('data.bisa_agresif', true);

        $this->assertDatabaseHas('groomers', [
            'id' => $groomer->id,
            'level_skill' => 'intermediate',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);
    }

    public function test_groomer_level_skill_is_required(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->putJson(route('admin.groomer.update', $groomer), [
                'level_skill' => '',
                'bisa_kucing' => false,
                'bisa_agresif' => false,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('level_skill');
    }

    public function test_groomer_capabilities_are_required(): void
    {
        $admin = $this->createUserWithRole('admin');
        $groomer = $this->createGroomer();

        $response = $this->actingAs($admin)
            ->putJson(route('admin.groomer.update', $groomer), [
                'level_skill' => 'dasar',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'bisa_kucing',
                'bisa_agresif',
            ]);
    }

    public function test_receptionist_cannot_access_groomer_management(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.groomer.index'));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_access_groomer_management(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $response = $this->actingAs($groomer)
            ->getJson(route('admin.groomer.index'));

        $response->assertForbidden();
    }
}