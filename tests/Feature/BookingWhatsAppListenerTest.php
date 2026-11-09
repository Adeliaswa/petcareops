<?php

namespace Tests\Feature;

use App\Events\BookingDibuat;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\NotificationLog;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWhatsAppListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(
            Carbon::parse('2026-11-09 08:00:00', 'Asia/Jakarta')
        );

        config(['services.fonnte.token' => null]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function buatBooking(): array
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

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $groomer = Groomer::create([
            'user_id' => $user->id,
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

        $mulai = now('Asia/Jakarta')->addMinutes(30);
        $tambahan = (30 - ($mulai->minute % 30)) % 30;

        $mulai = $mulai->addMinutes($tambahan)->setSecond(0);

        $appointment = app(BookingService::class)->buat([
            'no_wa' => $customer->no_wa,
            'customer_id' => $customer->id,
            'pet_id' => $pet->id,
            'service_ids' => [$service->id],
            'tanggal' => $mulai->toDateString(),
            'mulai' => $mulai->format('Y-m-d H:i:s'),
            'idempotency_key' => str()->uuid()->toString(),
        ], 'walk-in');

        return compact('customer', 'pet', 'service', 'appointment');
    }

    public function test_listener_mengirim_konfirmasi_booking_dan_mencatat_fallback(): void
    {
        $data = $this->buatBooking();

        event(new BookingDibuat($data['appointment']));

        $log = NotificationLog::where('jenis', 'konfirmasi_booking')
            ->where('appointment_id', $data['appointment']->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('fallback', $log->status);
        $this->assertSame('6281234567890', $log->nomor_tujuan);

        $this->assertStringContainsString('Milo', $log->pesan);
        $this->assertStringContainsString('Full Grooming', $log->pesan);
        $this->assertStringContainsString(
            route('booking.status', ['token' => $data['appointment']->token]),
            $log->pesan
        );
    }

    public function test_listener_tidak_mencatat_konfirmasi_dua_kali(): void
    {
        $data = $this->buatBooking();

        event(new BookingDibuat($data['appointment']));
        event(new BookingDibuat($data['appointment']));

        $jumlahLog = NotificationLog::where('jenis', 'konfirmasi_booking')
            ->where('appointment_id', $data['appointment']->id)
            ->count();

        $this->assertSame(1, $jumlahLog);
    }
}