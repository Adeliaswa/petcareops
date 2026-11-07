<?php

namespace Tests\Feature;

use App\Models\DurationFactor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DurationFactorTest extends TestCase
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

    public function test_admin_can_create_duration_factor(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.faktor-durasi.store'), [
                'kategori' => 'ukuran',
                'key' => 'ekstra_besar',
                'label' => 'Ekstra Besar',
                'multiplier' => 1.800,
                'extra_minutes' => 10,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.key', 'ekstra_besar');

        $this->assertDatabaseHas('duration_factors', [
            'kategori' => 'ukuran',
            'key' => 'ekstra_besar',
        ]);
    }

    public function test_duration_factor_multiplier_must_be_greater_than_zero(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.faktor-durasi.store'), [
                'kategori' => 'ukuran',
                'key' => 'ekstra_besar',
                'label' => 'Ekstra Besar',
                'multiplier' => 0,
                'extra_minutes' => 0,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('multiplier');
    }

    public function test_extra_minutes_cannot_be_negative(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.faktor-durasi.store'), [
                'kategori' => 'ukuran',
                'key' => 'ekstra_besar',
                'label' => 'Ekstra Besar',
                'multiplier' => 1.5,
                'extra_minutes' => -5,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('extra_minutes');
    }

    public function test_admin_can_update_duration_factor(): void
    {
        $admin = $this->createUserWithRole('admin');

        $factor = DurationFactor::create([
            'kategori' => 'ukuran',
            'key' => 'besar',
            'label' => 'Besar',
            'multiplier' => 1.500,
            'extra_minutes' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->putJson(route('admin.faktor-durasi.update', $factor), [
                'label' => 'Besar Sekali',
                'multiplier' => 1.700,
                'extra_minutes' => 5,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.label', 'Besar Sekali');
    }

    public function test_factor_category_and_key_cannot_be_updated(): void
    {
        $admin = $this->createUserWithRole('admin');

        $factor = DurationFactor::create([
            'kategori' => 'ukuran',
            'key' => 'besar',
            'label' => 'Besar',
            'multiplier' => 1.500,
            'extra_minutes' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->putJson(route('admin.faktor-durasi.update', $factor), [
                'kategori' => 'perilaku',
                'key' => 'agresif',
                'label' => 'Besar',
                'multiplier' => 1.500,
                'extra_minutes' => 0,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('duration_factors', [
            'id' => $factor->id,
            'kategori' => 'ukuran',
            'key' => 'besar',
        ]);
    }

    public function test_admin_cannot_delete_factor_used_by_pet(): void
    {
        $admin = $this->createUserWithRole('admin');

        $factor = DurationFactor::create([
            'kategori' => 'ukuran',
            'key' => 'besar',
            'label' => 'Besar',
            'multiplier' => 1.500,
            'extra_minutes' => 0,
        ]);

        $this->createPetUsingFactor('besar');

        $response = $this->actingAs($admin)
            ->deleteJson(route('admin.faktor-durasi.destroy', $factor));

        $response->assertUnprocessable();

        $this->assertDatabaseHas('duration_factors', [
            'id' => $factor->id,
        ]);
    }

    public function test_admin_can_delete_unused_factor(): void
    {
        $admin = $this->createUserWithRole('admin');

        $factor = DurationFactor::create([
            'kategori' => 'ukuran',
            'key' => 'ekstra_besar',
            'label' => 'Ekstra Besar',
            'multiplier' => 1.800,
            'extra_minutes' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('admin.faktor-durasi.destroy', $factor));

        $response->assertOk();

        $this->assertDatabaseMissing('duration_factors', [
            'id' => $factor->id,
        ]);
    }

    public function test_receptionist_cannot_access_duration_factor_management(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        $response = $this->actingAs($receptionist)
            ->getJson(route('admin.faktor-durasi.index'));

        $response->assertForbidden();
    }

    public function test_groomer_cannot_access_duration_factor_management(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $response = $this->actingAs($groomer)
            ->getJson(route('admin.faktor-durasi.index'));

        $response->assertForbidden();
    }

    private function createPetUsingFactor(string $size): void
    {
        $customer = \App\Models\Customer::create([
            'nama' => 'Pemilik Test',
            'no_wa' => '6281234567890',
        ]);

        \App\Models\Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ukuran' => $size,
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);
    }
}