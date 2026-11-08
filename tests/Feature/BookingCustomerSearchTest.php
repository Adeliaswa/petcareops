<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingCustomerSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('resepsionis', 'web');
        Role::findOrCreate('groomer', 'web');
    }

    private function buatResepsionis(): User
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('resepsionis');

        return $user;
    }

    public function test_resepsionis_can_search_customer_by_name(): void
    {
        $customer = Customer::create([
            'nama' => 'Adelia Putri',
            'no_wa' => '6281234567890',
        ]);

        $this->actingAs($this->buatResepsionis())
            ->getJson('/staf/booking/pelanggan?q=Adelia')
            ->assertOk()
            ->assertJsonPath('data.0.id', $customer->id)
            ->assertJsonPath('data.0.nama', 'Adelia Putri');
    }

    public function test_resepsionis_can_search_customer_by_whatsapp(): void
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Pengujian',
            'no_wa' => '6281234567890',
        ]);

        $this->actingAs($this->buatResepsionis())
            ->getJson('/staf/booking/pelanggan?q=628123')
            ->assertOk()
            ->assertJsonPath('data.0.id', $customer->id);
    }

    public function test_groomer_cannot_search_customers(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('groomer');

        $this->actingAs($user)
            ->getJson('/staf/booking/pelanggan?q=Adelia')
            ->assertForbidden();
    }

    public function test_guest_cannot_search_customers(): void
    {
        $this->getJson('/staf/booking/pelanggan?q=Adelia')
            ->assertUnauthorized();
    }

    public function test_search_requires_query(): void
    {
        $this->actingAs($this->buatResepsionis())
            ->getJson('/staf/booking/pelanggan')
            ->assertUnprocessable();
    }
}
