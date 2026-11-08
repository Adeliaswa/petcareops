<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Carbon\Carbon;

class BookingWalkInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
{
    parent::setUp();

    Carbon::setTestNow(
        Carbon::parse('2026-11-09 08:00:00', 'Asia/Jakarta')
    );
}

protected function tearDown(): void
{
    Carbon::setTestNow();

    parent::tearDown();
}
    private function siapkanWalkIn(): array
    {

        $this->seed(DurationFactorSeeder::class);

        Role::firstOrCreate([
            'name' => 'resepsionis',
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

        $resepsionis = User::factory()->create();
        $resepsionis->assignRole('resepsionis');

        $customer = Customer::create([
            'nama' => 'Pelanggan Walk In',
            'no_wa' => '6281234567890',
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $userGroomer = User::factory()->create([
            'is_active' => true,
        ]);

        $groomer = Groomer::create([
            'user_id' => $userGroomer->id,
            'level_skill' => 'ahli',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $hari = now('Asia/Jakarta')->dayOfWeekIso;

        $groomer->shifts()->create([
            'hari' => $hari,
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:00',
        ]);

        AppSetting::create([
            'key' => 'slot_interval',
            'value' => '30',
        ]);

        AppSetting::create([
            'key' => 'skill_minimum_kecil',
            'value' => 'dasar',
        ]);

        $service = Service::create([
            'nama' => 'Full Grooming',
            'deskripsi' => 'Layanan grooming lengkap',
            'durasi_dasar' => 60,
            'harga' => 100000,
            'aktif' => true,
        ]);

        return [
            'resepsionis' => $resepsionis,
            'customer' => $customer,
            'pet' => $pet,
            'groomer' => $groomer,
            'service' => $service,
        ];
    }

    private function dataBooking(array $data): array
    {
        $mulai = now('Asia/Jakarta')->addMinutes(30);

        $menitTambahan = (30 - ($mulai->minute % 30)) % 30;

        $mulai = $mulai
            ->addMinutes($menitTambahan)
            ->setSecond(0);

        return [
            'no_wa' => $data['customer']->no_wa,
            'customer_id' => $data['customer']->id,
            'pet_id' => $data['pet']->id,
            'service_ids' => [$data['service']->id],
            'tanggal' => $mulai->toDateString(),
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'idempotency_key' => str()->uuid()->toString(),
        ];
    }

    public function test_resepsionis_can_create_walk_in_booking(): void
    {
        $data = $this->siapkanWalkIn();

        $response = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $this->dataBooking($data));

        // Tampilkan detail jika server mengembalikan error.
        if ($response->status() !== 201) {
            $response->dump();
        }

        $response
            ->assertCreated()
            ->assertJsonPath('booking.sumber', 'walk-in')
            ->assertJsonPath('booking.status', 'menunggu')
            ->assertJsonPath('booking.pet', 'Milo');

        $this->assertDatabaseHas('appointments', [
            'pet_id' => $data['pet']->id,
            'groomer_id' => $data['groomer']->id,
            'sumber' => 'walk-in',
            'status' => 'menunggu',
        ]);

        $this->assertDatabaseHas('appointments', [
            'token' => $response->json('booking.token'),
        ]);
    }

    public function test_groomer_cannot_create_walk_in_booking(): void
    {
        $data = $this->siapkanWalkIn();

        $groomerUser = $data['groomer']->user;
        $groomerUser->assignRole('groomer');

        $response = $this
            ->actingAs($groomerUser)
            ->postJson(
                '/staf/booking/konfirmasi',
                $this->dataBooking($data)
            );

        $response->assertForbidden();

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_guest_cannot_create_walk_in_booking(): void
    {
        $data = $this->siapkanWalkIn();

        $response = $this->postJson(
            '/staf/booking/konfirmasi',
            $this->dataBooking($data)
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_walk_in_dapat_dibuat_kurang_dari_60_menit(): void
    {
        $data = $this->siapkanWalkIn();

        $mulai = now('Asia/Jakarta')
            ->addMinutes(30)
            ->setSecond(0);

        $menitTambahan = (30 - ($mulai->minute % 30)) % 30;

        $mulai->addMinutes($menitTambahan);

        $payload = $this->dataBooking($data);
        $payload['tanggal'] = $mulai->toDateString();
        $payload['mulai'] = $mulai->format('Y-m-d H:i:s');

        $selisihMenit = now('Asia/Jakarta')->diffInMinutes($mulai);

        $this->assertLessThan(60, $selisihMenit);

        $response = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('booking.sumber', 'walk-in');
    }

    public function test_endpoint_walk_in_menolak_konfirmasi_ganda(): void
    {
        $data = $this->siapkanWalkIn();
        $payload = $this->dataBooking($data);

        $responsePertama = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $payload);

        $responsePertama->assertCreated();

        $responseKedua = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $payload);

        $responseKedua
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Permintaan booking ini sudah pernah diproses.'
            );

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_pelanggan_dan_pet_baru_disimpan_saat_booking_berhasil(): void
    {
        $data = $this->siapkanWalkIn();

        $mulai = now('Asia/Jakarta')
            ->addMinutes(30)
            ->setSecond(0);

        $menitTambahan = (30 - ($mulai->minute % 30)) % 30;
        $mulai->addMinutes($menitTambahan);

        $payload = [
            'no_wa' => '6289876543210',
            'nama_pelanggan' => 'Pelanggan Baru',
            'pet' => [
                'nama' => 'Bobby',
                'spesies' => 'anjing',
                'ras' => 'Golden Retriever',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'panjang',
                'perilaku' => 'normal',
            ],
            'service_ids' => [$data['service']->id],
            'tanggal' => $mulai->toDateString(),
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'idempotency_key' => str()->uuid()->toString(),
        ];

        $this->assertDatabaseMissing('customers', [
            'no_wa' => '6289876543210',
        ]);

        $response = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $payload);

        $response->assertCreated()
            ->assertJsonPath('booking.sumber', 'walk-in')
            ->assertJsonPath('booking.pet', 'Bobby');

        $customerBaru = Customer::where(
            'no_wa',
            '6289876543210'
        )->firstOrFail();

        $this->assertSame('Pelanggan Baru', $customerBaru->nama);

        $petBaru = Pet::where('customer_id', $customerBaru->id)
            ->where('nama', 'Bobby')
            ->firstOrFail();

        $this->assertDatabaseHas('appointments', [
            'pet_id' => $petBaru->id,
            'sumber' => 'walk-in',
        ]);

        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseCount('pets', 2);
    }

    public function test_pelanggan_lama_dapat_mendaftarkan_pet_baru_tanpa_duplikasi_customer(): void
    {
        $data = $this->siapkanWalkIn();

        $mulai = now('Asia/Jakarta')
            ->addMinutes(30)
            ->setSecond(0);

        $menitTambahan = (30 - ($mulai->minute % 30)) % 30;
        $mulai->addMinutes($menitTambahan);

        $payload = [
            'no_wa' => $data['customer']->no_wa,
            'pet' => [
                'nama' => 'Bobby',
                'spesies' => 'anjing',
                'ras' => 'Golden Retriever',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'panjang',
                'perilaku' => 'normal',
            ],
            'service_ids' => [$data['service']->id],
            'tanggal' => $mulai->toDateString(),
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'idempotency_key' => str()->uuid()->toString(),
        ];

        $response = $this
            ->actingAs($data['resepsionis'])
            ->postJson('/staf/booking/konfirmasi', $payload);

        $response->assertCreated()
            ->assertJsonPath('booking.pet', 'Bobby');

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('pets', 2);

        $this->assertDatabaseHas('pets', [
            'customer_id' => $data['customer']->id,
            'nama' => 'Bobby',
        ]);
    }
}
