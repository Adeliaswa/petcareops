<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'resepsionis',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'groomer',
            'guard_name' => 'web',
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_view_customer_list(): void
    {
        $admin = $this->createUserWithRole('admin');

        Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6281234567890',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('staf.pemilik.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'nama' => 'Budi',
                'no_wa' => '6281234567890',
            ]);
    }

    public function test_receptionist_can_store_customer_with_normalized_phone(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        $response = $this->actingAs($receptionist)
            ->postJson(route('staf.pemilik.store'), [
                'nama' => 'Ani',
                'no_wa' => '0812 3456 7890',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.no_wa', '6281234567890');

        $this->assertDatabaseHas('customers', [
            'nama' => 'Ani',
            'no_wa' => '6281234567890',
        ]);
    }

    public function test_customer_phone_number_must_be_unique(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');

        Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6281234567890',
        ]);

        $response = $this->actingAs($receptionist)
            ->postJson(route('staf.pemilik.store'), [
                'nama' => 'Ani',
                'no_wa' => '0812-3456-7890',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('no_wa');
    }

    public function test_groomer_cannot_access_customer_list(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $response = $this->actingAs($groomer)
            ->getJson(route('staf.pemilik.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_customer(): void
    {
        $admin = $this->createUserWithRole('admin');

        $customer = Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6281234567890',
        ]);

        $response = $this->actingAs($admin)
            ->putJson(route('staf.pemilik.update', $customer), [
                'nama' => 'Budi Santoso',
                'no_wa' => '081234567890',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.nama', 'Budi Santoso')
            ->assertJsonPath('data.no_wa', '6281234567890');
    }

    public function test_admin_can_soft_delete_customer_without_pets(): void
    {
        $admin = $this->createUserWithRole('admin');

        $customer = Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6281234567890',
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('staf.pemilik.destroy', $customer));

        $response->assertOk();

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_can_be_searched_by_partial_name(): void
    {
        $admin = $this->createUserWithRole('admin');

        Customer::create([
            'nama' => 'Adelia Swas',
            'no_wa' => '6281234567890',
        ]);

        Customer::create([
            'nama' => 'Budi Santoso',
            'no_wa' => '6289876543210',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('staf.pemilik.index', ['search' => 'delia']));

        $response->assertOk()
            ->assertJsonFragment([
                'nama' => 'Adelia Swas',
                'no_wa' => '6281234567890',
            ])
            ->assertJsonMissing([
                'nama' => 'Budi Santoso',
                'no_wa' => '6289876543210',
            ]);
    }

    public function test_customer_can_be_searched_by_phone_number(): void
    {
        $admin = $this->createUserWithRole('admin');

        Customer::create([
            'nama' => 'Adelia',
            'no_wa' => '6281234567890',
        ]);

        Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6289876543210',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('staf.pemilik.index', ['search' => '81234']));

        $response->assertOk()
            ->assertJsonFragment([
                'nama' => 'Adelia',
                'no_wa' => '6281234567890',
            ])
            ->assertJsonMissing([
                'nama' => 'Budi',
                'no_wa' => '6289876543210',
            ]);
    }
}