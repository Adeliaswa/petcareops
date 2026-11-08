<?php

namespace Tests\Feature;

use App\Events\BookingDibuat;
use App\Exceptions\BookingException;
use App\Models\AppointmentService;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
use App\Services\DurationCalculator;
use App\Services\SlotFinderService;
use Carbon\Carbon;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingServiceTest extends TestCase
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

    private function siapkanData(): array
    {
        $this->seed(DurationFactorSeeder::class);

        $customer = Customer::create([
            'nama' => 'Pelanggan Tes',
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

        $groomer->shifts()->create([
            'hari' => now('Asia/Jakarta')->dayOfWeekIso,
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

        return compact(
            'customer',
            'pet',
            'groomer',
            'service'
        );
    }

    private function dataBooking(array $data): array
    {
        $mulai = now('Asia/Jakarta')->addMinutes(30);

        $tambahan = (30 - ($mulai->minute % 30)) % 30;

        $mulai = $mulai
            ->addMinutes($tambahan)
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

    public function test_booking_menyimpan_snapshot_layanan_dan_durasi(): void
    {
        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);

        $appointment = app(BookingService::class)->buat(
            $payload,
            'walk-in'
        );

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'pet_id' => $data['pet']->id,
            'groomer_id' => $data['groomer']->id,
            'status' => 'menunggu',
            'sumber' => 'walk-in',
        ]);

        $snapshot = AppointmentService::where(
            'appointment_id',
            $appointment->id
        )->firstOrFail();

        $this->assertSame('Full Grooming', $snapshot->nama_layanan);
        $this->assertEquals(60, $snapshot->durasi_dasar);
        $this->assertEquals(100000, $snapshot->harga);

        $durasiTersimpan = $appointment->durasi_estimasi;
        $rincianTersimpan = $appointment->rincian_estimasi;

        $this->assertGreaterThan(0, $durasiTersimpan);
        $this->assertNotEmpty($rincianTersimpan);

        $this->assertEquals(
            $durasiTersimpan,
            $appointment->mulai->diffInMinutes(
                $appointment->selesai_estimasi
            )
        );

        // Ubah master layanan setelah booking dibuat.
        $data['service']->update([
            'nama' => 'Layanan Baru',
            'durasi_dasar' => 120,
            'harga' => 200000,
        ]);

        $snapshot->refresh();

        // Snapshot booking lama tidak boleh ikut berubah.
        $this->assertSame('Full Grooming', $snapshot->nama_layanan);
        $this->assertEquals(60, $snapshot->durasi_dasar);
        $this->assertEquals(100000, $snapshot->harga);

        $appointment->refresh();

        $this->assertEquals(
            $durasiTersimpan,
            $appointment->durasi_estimasi
        );

        $this->assertSame(
            $rincianTersimpan,
            $appointment->rincian_estimasi
        );

        $this->assertEquals(
            $appointment->mulai->copy()
                ->addMinutes($durasiTersimpan)->timestamp,
            $appointment->selesai_estimasi->timestamp
        );

        $this->assertSame('menunggu', $appointment->status);
    }

    public function test_idempotency_key_mencegah_booking_ganda(): void
    {
        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);

        $service = app(BookingService::class);

        $bookingPertama = $service->buat($payload, 'walk-in');

        try {
            $service->buat($payload, 'walk-in');

            $this->fail('Booking kedua seharusnya ditolak.');
        } catch (BookingException $e) {
            $this->assertSame(
                'Permintaan booking ini sudah pernah diproses.',
                $e->getMessage()
            );
        }

        $this->assertDatabaseCount('appointments', 1);

        $this->assertDatabaseHas('appointments', [
            'id' => $bookingPertama->id,
            'idempotency_key' => $payload['idempotency_key'],
        ]);
    }

    public function test_transaksi_rollback_jika_layanan_tidak_ditemukan(): void
    {
        $data = $this->siapkanData();

        $mulai = now('Asia/Jakarta')
            ->addMinutes(60)
            ->setSecond(0);

        $jumlahCustomerSebelum = Customer::count();
        $jumlahPetSebelum = Pet::count();

        $payload = [
            'no_wa' => '6289876543210',
            'nama_pelanggan' => 'Pelanggan Baru',
            'pet' => [
                'nama' => 'Bobby',
                'spesies' => 'anjing',
                'ras' => 'Poodle',
                'ukuran' => 'kecil',
                'jenis_bulu' => 'panjang',
                'perilaku' => 'normal',
            ],
            'service_ids' => [999999],
            'tanggal' => $mulai->toDateString(),
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'idempotency_key' => str()->uuid()->toString(),
        ];

        try {
            app(BookingService::class)->buat(
                $payload,
                'walk-in'
            );

            $this->fail(
                'Booking seharusnya gagal karena layanan tidak ditemukan.'
            );
        } catch (BookingException $e) {
            $this->assertStringContainsString(
                'layanan tidak ditemukan',
                strtolower($e->getMessage())
            );
        }

        $this->assertSame(
            $jumlahCustomerSebelum,
            Customer::count()
        );

        $this->assertSame(
            $jumlahPetSebelum,
            Pet::count()
        );

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_event_booking_dibuat_dipanggil_setelah_booking_berhasil(): void
    {
        Event::fake([BookingDibuat::class]);

        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);

        $appointment = app(BookingService::class)->buat(
            $payload,
            'walk-in'
        );

        Event::assertDispatched(
            BookingDibuat::class,
            fn ($event) => $event->appointment->id === $appointment->id
        );
    }

    public function test_event_booking_dibuat_tidak_dipanggil_jika_booking_gagal(): void
    {
        Event::fake([BookingDibuat::class]);

        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);

        $payload['service_ids'] = [999999];

        try {
            app(BookingService::class)->buat($payload, 'walk-in');

            $this->fail('Booking seharusnya gagal.');
        } catch (BookingException) {
            Event::assertNotDispatched(BookingDibuat::class);
        }
    }

    public function test_pet_tidak_boleh_memiliki_dua_booking_aktif_yang_bertumpukan(): void
    {
        $data = $this->siapkanData();
        $payloadPertama = $this->dataBooking($data);

        $service = app(BookingService::class);

        $service->buat($payloadPertama, 'walk-in');

        $groomerKedua = Groomer::create([
            'user_id' => User::factory()->create([
                'is_active' => true,
            ])->id,
            'level_skill' => 'ahli',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $groomerKedua->shifts()->create([
            'hari' => now('Asia/Jakarta')->dayOfWeekIso,
            'jam_mulai' => '00:00:00',
            'jam_selesai' => '23:59:00',
        ]);

        $payloadKedua = $payloadPertama;
        $payloadKedua['idempotency_key'] = str()->uuid()->toString();

        try {
            $service->buat($payloadKedua, 'walk-in');

            $this->fail(
                'Booking kedua untuk pet yang sama seharusnya ditolak.'
            );
        } catch (BookingException $e) {
            $this->assertSame(
                'Hewan sudah memiliki jadwal pada waktu tersebut.',
                $e->getMessage()
            );
        }

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_booking_menerima_catatan_pelanggan_maksimal_500_karakter(): void
    {
        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);
        $payload['catatan_pelanggan'] = str_repeat('A', 500);

        $appointment = app(BookingService::class)->buat(
            $payload,
            'walk-in'
        );

        $this->assertSame(
            str_repeat('A', 500),
            $appointment->fresh()->catatan_pelanggan
        );
    }

    public function test_booking_menolak_catatan_pelanggan_lebih_dari_500_karakter(): void
    {
        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);
        $payload['catatan_pelanggan'] = str_repeat('A', 501);

        try {
            app(BookingService::class)->buat($payload, 'walk-in');

            $this->fail(
                'Catatan lebih dari 500 karakter seharusnya ditolak.'
            );
        } catch (BookingException $e) {
            $this->assertStringContainsString(
                'must not be greater than 500 characters',
                $e->getMessage()
            );
        }

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_booking_menolak_layanan_yang_tidak_aktif(): void
    {
        $data = $this->siapkanData();
        $payload = $this->dataBooking($data);

        $data['service']->update([
            'aktif' => false,
        ]);

        try {
            app(BookingService::class)->buat($payload, 'walk-in');

            $this->fail('Layanan tidak aktif seharusnya ditolak.');
        } catch (BookingException $e) {
            $this->assertSame(
                'Layanan Full Grooming sudah tidak aktif.',
                $e->getMessage()
            );
        }

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_booking_menolak_slot_lama_jika_durasi_layanan_berubah(): void
    {
        Carbon::setTestNow(
            Carbon::parse('2026-11-09 08:00:00', 'Asia/Jakarta')
        );

        try {
            $data = $this->siapkanData();

            $mulai = Carbon::parse(
                '2026-11-09 10:00:00',
                'Asia/Jakarta'
            );

            $payload = $this->dataBooking($data);
            $payload['tanggal'] = $mulai->toDateString();
            $payload['mulai'] = $mulai->format('Y-m-d H:i:s');

            $durasiCalculator = app(DurationCalculator::class);
            $slotFinder = app(SlotFinderService::class);

            $estimasiAwal = $durasiCalculator->hitung(
                $data['pet'],
                [$data['service']->id],
                []
            );

            $durasiAwal = $estimasiAwal->menit_total;

            $hasilSlotAwal = $slotFinder->cari(
                $payload['tanggal'],
                $durasiAwal,
                $data['pet'],
                [$data['service']->id]
            );

            $this->assertContains(
                $payload['mulai'],
                collect($hasilSlotAwal->slots)->pluck('mulai')->all()
            );

            $data['groomer']->shifts()->first()->update([
                'jam_selesai' => $mulai
                    ->copy()
                    ->addMinutes($durasiAwal + 30)
                    ->format('H:i:s'),
            ]);

            $data['service']->update([
                'durasi_dasar' => $data['service']->durasi_dasar + 60,
            ]);

            try {
                app(BookingService::class)->buat(
                    $payload,
                    'publik'
                );

                $this->fail(
                    'Booking seharusnya ditolak karena slot lama tidak lagi tersedia.'
                );
            } catch (BookingException $e) {
                $this->assertSame(
                    'Slot baru saja terisi, silakan pilih slot lain.',
                    $e->getMessage()
                );
            }

            $this->assertDatabaseCount('appointments', 0);
        } finally {
            Carbon::setTestNow();
        }
    }
}