<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AppSettingSeeder;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingSlotTest extends TestCase
{
    use RefreshDatabase;

    protected User $groomerUser;
    protected Groomer $groomer;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            Carbon::parse('2026-11-08 08:00:00', 'Asia/Jakarta')
        );

        $this->seed([
            DurationFactorSeeder::class,
            AppSettingSeeder::class,
        ]);

        $this->groomerUser = User::factory()->create([
            'name' => 'Groomer Test',
            'is_active' => true,
        ]);

        $this->groomer = Groomer::create([
            'user_id' => $this->groomerUser->id,
            'level_skill' => 'dasar',
            'bisa_kucing' => false,
            'bisa_agresif' => false,
        ]);

        // 1 = Senin
        $this->groomer->shifts()->create([
            'hari' => 1,
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        $this->service = Service::create([
            'nama' => 'Mandi',
            'deskripsi' => 'Layanan mandi hewan',
            'durasi_dasar' => 30,
            'harga' => 50000,
            'aktif' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function payload(): array
    {
        return [
            'pet' => [
                'spesies' => 'anjing',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'pendek',
                'perilaku' => 'normal',
            ],
            'service_ids' => [$this->service->id],
            'tanggal' => '2026-11-09',
        ];
    }

    public function test_public_slot_response_does_not_expose_groomer_identity(): void
    {
        $response = $this->postJson(
            '/booking/slot',
            $this->payload()
        );

        $response
            ->assertOk()
            ->assertJsonPath('durasi_menit', 30)
            ->assertJsonStructure([
                'durasi_menit',
                'slots' => [
                    '*' => [
                        'mulai',
                        'selesai',
                    ],
                ],
                'alasan',
            ])
            ->assertJsonMissingPath('slots.0.groomer_id')
            ->assertJsonMissingPath('slots.0.nama_groomer');

        $this->assertNotEmpty($response->json('slots'));
    }

    public function test_resepsionis_can_see_groomer_name_in_slot_response(): void
    {
        Role::findOrCreate('resepsionis', 'web');

        $resepsionis = User::factory()->create([
            'is_active' => true,
        ]);

        $resepsionis->assignRole('resepsionis');

        $response = $this->actingAs($resepsionis)->postJson(
            '/staf/booking/slot',
            $this->payload()
        );

        $response
            ->assertOk()
            ->assertJsonPath('durasi_menit', 30)
            ->assertJsonPath(
                'slots.0.nama_groomer',
                $this->groomerUser->name
            );

        $this->assertNotEmpty($response->json('slots'));
    }

    public function test_public_slot_tidak_menawarkan_jadwal_kurang_dari_60_menit(): void
    {
        Carbon::setTestNow(
            Carbon::parse('2026-11-09 08:00:00', 'Asia/Jakarta')
        );

        $this->groomer->shifts()->first()->update([
            'jam_mulai' => '08:30',
        ]);

        $response = $this->postJson(
            '/booking/slot',
            $this->payload()
        );

        $response->assertOk();

        $slots = $response->json('slots');

        $this->assertNotEmpty($slots);

        foreach ($slots as $slot) {
            $mulai = Carbon::parse($slot['mulai'], 'Asia/Jakarta');

            $this->assertGreaterThanOrEqual(
                Carbon::parse(
                    '2026-11-09 09:00:00',
                    'Asia/Jakarta'
                )->timestamp,
                $mulai->timestamp
            );
        }
    }

    public function test_cancelled_appointment_releases_slot_through_http(): void
    {
        Role::findOrCreate('resepsionis', 'web');

        $resepsionis = User::factory()->create([
            'is_active' => true,
        ]);

        $resepsionis->assignRole('resepsionis');

        $customer = Customer::create([
            'nama' => 'Customer Test',
            'no_wa' => '081234567890',
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $this->groomer->id,
            'mulai' => Carbon::parse(
                '2026-11-09 09:00:00',
                'Asia/Jakarta'
            ),
            'selesai_estimasi' => Carbon::parse(
                '2026-11-09 09:30:00',
                'Asia/Jakarta'
            ),
            'durasi_estimasi' => 30,
            'status' => 'menunggu',
            'token' => bin2hex(random_bytes(32)),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        // Pastikan jadwal 09.00 awalnya tidak tersedia.
        $slotAwal = $this->postJson(
            '/booking/slot',
            $this->payload()
        );

        $slotAwal->assertOk();

        $this->assertNotContains(
            '2026-11-09T09:00:00',
            collect($slotAwal->json('slots'))
                ->pluck('mulai')
                ->map(fn ($mulai) => Carbon::parse($mulai)->format('Y-m-d\TH:i:s'))
                ->all()
        );

        // Resepsionis membatalkan appointment.
        $this->actingAs($resepsionis)
    ->post(
        route('staf.appointments.cancel', $appointment),
        [
            'alasan_batal' => 'Pelanggan membatalkan booking.',
        ]
    )
    ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'dibatalkan',
            'alasan_batal' => 'Pelanggan membatalkan booking.',
        ]);

        // Pastikan slot 09.00 tersedia kembali.
        $slotSetelahBatal = $this->postJson(
            '/booking/slot',
            $this->payload()
        );

        $slotSetelahBatal->assertOk();

        $waktuTersedia = collect($slotSetelahBatal->json('slots'))
            ->pluck('mulai')
            ->map(fn ($mulai) => Carbon::parse($mulai)->format('Y-m-d\TH:i:s'))
            ->all();

        $this->assertContains(
            '2026-11-09T09:00:00',
            $waktuTersedia
        );
    }
}