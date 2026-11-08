<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingEstimationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DurationFactorSeeder::class);
    }

    public function test_public_user_can_get_duration_estimation(): void
    {
        $layananMandi = Service::create([
            'nama' => 'Mandi',
            'deskripsi' => 'Layanan mandi hewan',
            'durasi_dasar' => 30,
            'harga' => 50000,
            'aktif' => true,
        ]);

        $layananPotongKuku = Service::create([
            'nama' => 'Potong Kuku',
            'deskripsi' => 'Layanan potong kuku',
            'durasi_dasar' => 15,
            'harga' => 20000,
            'aktif' => true,
        ]);

        $response = $this->postJson('/booking/estimasi', [
            'pet' => [
                'spesies' => 'anjing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'pendek',
                'perilaku' => 'normal',
            ],
            'service_ids' => [
                $layananMandi->id,
                $layananPotongKuku->id,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('durasi_menit', 45);
    }

    public function test_public_estimation_rejects_empty_service_list(): void
    {
        $response = $this->postJson('/booking/estimasi', [
            'pet' => [
                'spesies' => 'anjing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'pendek',
                'perilaku' => 'normal',
            ],
            'service_ids' => [],
        ]);

        $response->assertUnprocessable();
    }

    public function test_resepsionis_can_access_staff_estimation(): void
    {
        Role::findOrCreate('resepsionis', 'web');

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('resepsionis');

        $layanan = Service::create([
            'nama' => 'Mandi',
            'deskripsi' => 'Layanan mandi hewan',
            'durasi_dasar' => 30,
            'harga' => 50000,
            'aktif' => true,
        ]);

        $response = $this->actingAs($user)->postJson(
            '/staf/booking/estimasi',
            [
                'pet' => [
                    'spesies' => 'anjing',
                    'ukuran' => 'kecil',
                    'jenis_bulu' => 'pendek',
                    'perilaku' => 'normal',
                ],
                'service_ids' => [$layanan->id],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('durasi_menit', 30);
    }

    public function test_groomer_cannot_access_staff_estimation(): void
    {
        Role::findOrCreate('groomer', 'web');

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $user->assignRole('groomer');

        $response = $this->actingAs($user)->postJson(
            '/staf/booking/estimasi',
            [
                'pet' => [
                    'spesies' => 'anjing',
                    'ukuran' => 'kecil',
                    'jenis_bulu' => 'pendek',
                    'perilaku' => 'normal',
                ],
                'service_ids' => [1],
            ]
        );

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_staff_estimation(): void
    {
        $response = $this->postJson('/staf/booking/estimasi', [
            'pet' => [
                'spesies' => 'anjing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'pendek',
                'perilaku' => 'normal',
            ],
            'service_ids' => [1],
        ]);

        $response->assertUnauthorized();
    }
}