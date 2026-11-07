<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DurationFactor;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetTest extends TestCase
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

        $factors = [
            ['kategori' => 'ukuran', 'key' => 'kecil', 'label' => 'Kecil'],
            ['kategori' => 'ukuran', 'key' => 'sedang', 'label' => 'Sedang'],
            ['kategori' => 'jenis_bulu', 'key' => 'pendek', 'label' => 'Pendek'],
            ['kategori' => 'jenis_bulu', 'key' => 'panjang', 'label' => 'Panjang'],
            ['kategori' => 'perilaku', 'key' => 'normal', 'label' => 'Normal'],
            ['kategori' => 'perilaku', 'key' => 'agresif', 'label' => 'Agresif'],
        ];

        foreach ($factors as $factor) {
            DurationFactor::create([
                ...$factor,
                'multiplier' => 1.000,
                'extra_minutes' => 0,
            ]);
        }
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'nama' => 'Budi',
            'no_wa' => '6281234567890',
        ]);
    }

    public function test_receptionist_can_store_pet(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');
        $customer = $this->createCustomer();

        $response = $this->actingAs($receptionist)
            ->postJson(route('staf.hewan.store'), [
                'customer_id' => $customer->id,
                'nama' => 'Milo',
                'spesies' => 'kucing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'pendek',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.nama', 'Milo')
            ->assertJsonPath('data.perilaku', 'normal');

        $this->assertDatabaseHas('pets', [
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'perilaku' => 'normal',
        ]);
    }

    public function test_pet_size_must_exist_in_duration_factors(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');
        $customer = $this->createCustomer();

        $response = $this->actingAs($receptionist)
            ->postJson(route('staf.hewan.store'), [
                'customer_id' => $customer->id,
                'nama' => 'Milo',
                'spesies' => 'kucing',
                'ukuran' => 'ekstra_besar',
                'jenis_bulu' => 'pendek',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('ukuran');
    }

    public function test_pet_coat_type_must_exist_in_duration_factors(): void
    {
        $receptionist = $this->createUserWithRole('resepsionis');
        $customer = $this->createCustomer();

        $response = $this->actingAs($receptionist)
            ->postJson(route('staf.hewan.store'), [
                'customer_id' => $customer->id,
                'nama' => 'Milo',
                'spesies' => 'kucing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'keriting',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('jenis_bulu');
    }

    public function test_admin_can_view_pet_with_customer(): void
    {
        $admin = $this->createUserWithRole('admin');
        $customer = $this->createCustomer();

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'kucing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('staf.hewan.show', $pet));

        $response->assertOk()
            ->assertJsonPath('data.nama', 'Milo')
            ->assertJsonPath('data.customer.nama', 'Budi');
    }

    public function test_admin_can_update_pet(): void
    {
        $admin = $this->createUserWithRole('admin');
        $customer = $this->createCustomer();

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'kucing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $response = $this->actingAs($admin)
            ->putJson(route('staf.hewan.update', $pet), [
                'customer_id' => $customer->id,
                'nama' => 'Milo Baru',
                'spesies' => 'kucing',
                'ukuran' => 'sedang',
                'jenis_bulu' => 'panjang',
                'perilaku' => 'agresif',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.nama', 'Milo Baru')
            ->assertJsonPath('data.ukuran', 'sedang');
    }

    public function test_groomer_cannot_access_pet_list(): void
    {
        $groomer = $this->createUserWithRole('groomer');

        $response = $this->actingAs($groomer)
            ->getJson(route('staf.hewan.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_soft_delete_pet(): void
    {
        $admin = $this->createUserWithRole('admin');
        $customer = $this->createCustomer();

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'kucing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson(route('staf.hewan.destroy', $pet));

        $response->assertOk();

        $this->assertSoftDeleted('pets', [
            'id' => $pet->id,
        ]);
    }

    public function test_pet_can_be_searched_by_partial_name(): void
{
    $admin = $this->createUserWithRole('admin');
    $customer = $this->createCustomer();

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Mochi',
        'spesies' => 'kucing',
        'ukuran' => 'kecil',
        'jenis_bulu' => 'pendek',
        'perilaku' => 'normal',
    ]);

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Bruno',
        'spesies' => 'anjing',
        'ukuran' => 'sedang',
        'jenis_bulu' => 'panjang',
        'perilaku' => 'normal',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('staf.hewan.index', ['search' => 'och']));

    $response->assertOk()
        ->assertJsonFragment(['nama' => 'Mochi'])
        ->assertJsonMissing(['nama' => 'Bruno']);
}

public function test_pet_can_be_searched_by_species(): void
{
    $admin = $this->createUserWithRole('admin');
    $customer = $this->createCustomer();

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Milo',
        'spesies' => 'kucing',
        'ukuran' => 'kecil',
        'jenis_bulu' => 'pendek',
        'perilaku' => 'normal',
    ]);

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Bruno',
        'spesies' => 'anjing',
        'ukuran' => 'sedang',
        'jenis_bulu' => 'panjang',
        'perilaku' => 'normal',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('staf.hewan.index', ['search' => 'anjing']));

    $response->assertOk()
        ->assertJsonFragment(['nama' => 'Bruno'])
        ->assertJsonMissing(['nama' => 'Milo']);
}

public function test_pet_can_be_searched_by_breed(): void
{
    $admin = $this->createUserWithRole('admin');
    $customer = $this->createCustomer();

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Milo',
        'spesies' => 'anjing',
        'ras' => 'Golden Retriever',
        'ukuran' => 'sedang',
        'jenis_bulu' => 'panjang',
        'perilaku' => 'normal',
    ]);

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Mochi',
        'spesies' => 'kucing',
        'ras' => 'Persia',
        'ukuran' => 'kecil',
        'jenis_bulu' => 'pendek',
        'perilaku' => 'normal',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('staf.hewan.index', ['search' => 'golden']));

    $response->assertOk()
        ->assertJsonFragment(['nama' => 'Milo'])
        ->assertJsonMissing(['nama' => 'Mochi']);
}

public function test_pet_can_be_searched_by_customer_name(): void
{
    $admin = $this->createUserWithRole('admin');

    $customer = Customer::create([
        'nama' => 'Adelia Swas',
        'no_wa' => '6281234567890',
    ]);

    $otherCustomer = Customer::create([
        'nama' => 'Budi Santoso',
        'no_wa' => '6289876543210',
    ]);

    Pet::create([
        'customer_id' => $customer->id,
        'nama' => 'Mochi',
        'spesies' => 'kucing',
        'ukuran' => 'kecil',
        'jenis_bulu' => 'pendek',
        'perilaku' => 'normal',
    ]);

    Pet::create([
        'customer_id' => $otherCustomer->id,
        'nama' => 'Bruno',
        'spesies' => 'anjing',
        'ukuran' => 'sedang',
        'jenis_bulu' => 'panjang',
        'perilaku' => 'normal',
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('staf.hewan.index', ['search' => 'adelia']));

    $response->assertOk()
        ->assertJsonFragment(['nama' => 'Mochi'])
        ->assertJsonMissing(['nama' => 'Bruno']);
}
}