<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\GroomerShift;
use App\Models\GroomerTimeOff;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Services\SlotFinderService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SlotFinderServiceTest extends TestCase
{
    use RefreshDatabase;

    private SlotFinderService $service;
    private Pet $pet;
    private Service $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-11-08 08:00:00', 'Asia/Jakarta')
        );

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groomer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->dateTime('mulai');
            $table->dateTime('selesai_estimasi');
            $table->unsignedInteger('durasi_estimasi');
            $table->string('status');
            $table->timestamps();
        });

        $this->service = new SlotFinderService();

        $settings = [
            'slot_interval' => '30',
            'batas_minimum_pesan' => '60',
            'horizon_hari' => '14',
            'skill_minimum_kecil' => 'dasar',
            'skill_minimum_sedang' => 'menengah',
        ];

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $customer = Customer::create([
            'nama' => 'Pelanggan Test',
            'no_wa' => '081234567890',
        ]);

        $this->pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $this->layanan = Service::create([
            'nama' => 'Mandi',
            'deskripsi' => 'Layanan mandi',
            'durasi_dasar' => 45,
            'harga' => 50000,
            'aktif' => true,
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function buatGroomer(
        string $nama,
        string $skill = 'dasar',
        bool $bisaKucing = false,
        bool $bisaAgresif = false
    ): Groomer {
        $user = User::create([
            'name' => $nama,
            'email' => strtolower(str_replace(' ', '', $nama)) . '@test.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        return Groomer::create([
            'user_id' => $user->id,
            'level_skill' => $skill,
            'bisa_kucing' => $bisaKucing,
            'bisa_agresif' => $bisaAgresif,
        ]);
    }

    private function buatShift(
        Groomer $groomer,
        string $mulai = '09:00:00',
        string $selesai = '12:00:00'
    ): void {
        GroomerShift::create([
            'groomer_id' => $groomer->id,
            'hari' => 1,
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
        ]);
    }

    private function buatAppointment(
        Groomer $groomer,
        string $mulai,
        string $selesai,
        int $durasi,
        string $status = 'menunggu'
    ): void {
        DB::table('appointments')->insert([
            'groomer_id' => $groomer->id,
            'pet_id' => $this->pet->id,
            'mulai' => $mulai,
            'selesai_estimasi' => $selesai,
            'durasi_estimasi' => $durasi,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_groomer_with_available_shift_returns_slots(): void
    {
        $groomer = $this->buatGroomer('Groomer Satu');
        $this->buatShift($groomer, '09:00:00', '11:00:00');

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);
        $this->assertSame('2026-11-09 09:00:00', $hasil->slots[0]['mulai']);
        $this->assertSame($groomer->id, $hasil->slots[0]['groomer_id']);
    }

    public function test_appointment_blocks_overlapping_slot(): void
    {
        $groomer = $this->buatGroomer('Groomer Dua');
        $this->buatShift($groomer, '09:00:00', '11:00:00');

        $this->buatAppointment(
            $groomer,
            '2026-11-09 09:00:00',
            '2026-11-09 10:00:00',
            60
        );

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);
        $this->assertSame('2026-11-09 10:00:00', $hasil->slots[0]['mulai']);
    }

    public function test_cancelled_appointment_does_not_block_slot(): void
    {
        $groomer = $this->buatGroomer('Groomer Tiga');
        $this->buatShift($groomer, '09:00:00', '11:00:00');

        $this->buatAppointment(
            $groomer,
            '2026-11-09 09:00:00',
            '2026-11-09 10:00:00',
            60,
            'dibatalkan'
        );

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertSame('2026-11-09 09:00:00', $hasil->slots[0]['mulai']);
    }

    public function test_groomer_on_time_off_is_not_available(): void
    {
        $groomer = $this->buatGroomer('Groomer Empat');
        $this->buatShift($groomer, '09:00:00', '11:00:00');

        GroomerTimeOff::create([
            'groomer_id' => $groomer->id,
            'tanggal_mulai' => '2026-11-09',
            'tanggal_selesai' => '2026-11-09',
            'alasan' => 'Cuti',
        ]);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertEmpty($hasil->slots);
    }

    public function test_slot_is_assigned_to_groomer_with_lower_workload(): void
    {
        $groomerSatu = $this->buatGroomer('Groomer Lima');
        $groomerDua = $this->buatGroomer('Groomer Enam');

        $this->buatShift($groomerSatu, '09:00:00', '11:00:00');
        $this->buatShift($groomerDua, '09:00:00', '11:00:00');

        $this->buatAppointment(
            $groomerSatu,
            '2026-11-09 12:00:00',
            '2026-11-09 13:00:00',
            60
        );

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);
        $this->assertSame(
            $groomerDua->id,
            $hasil->slots[0]['groomer_id']
        );
    }

    public function test_cat_requires_groomer_with_cat_capability(): void
    {
        $this->pet->update(['spesies' => 'kucing']);

        $groomerTidakSesuai = $this->buatGroomer('Groomer Kucing Satu');
        $this->buatShift($groomerTidakSesuai);

        $groomerSesuai = $this->buatGroomer(
            'Groomer Kucing Dua',
            'dasar',
            true
        );
        $this->buatShift($groomerSesuai);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);

        foreach ($hasil->slots as $slot) {
            $this->assertSame($groomerSesuai->id, $slot['groomer_id']);
        }
    }

    public function test_aggressive_pet_requires_capable_groomer(): void
    {
        $this->pet->update(['perilaku' => 'agresif']);

        $groomerTidakSesuai = $this->buatGroomer('Groomer Agresif Satu');
        $this->buatShift($groomerTidakSesuai);

        $groomerSesuai = $this->buatGroomer(
            'Groomer Agresif Dua',
            'dasar',
            false,
            true
        );
        $this->buatShift($groomerSesuai);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);

        foreach ($hasil->slots as $slot) {
            $this->assertSame($groomerSesuai->id, $slot['groomer_id']);
        }
    }

    public function test_groomer_must_meet_minimum_skill_requirement(): void
    {
        $this->pet->update(['ukuran' => 'sedang']);

        $groomerDasar = $this->buatGroomer('Groomer Skill Dasar', 'dasar');
        $this->buatShift($groomerDasar);

        $groomerMenengah = $this->buatGroomer(
            'Groomer Skill Menengah',
            'menengah'
        );
        $this->buatShift($groomerMenengah);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);

        foreach ($hasil->slots as $slot) {
            $this->assertSame($groomerMenengah->id, $slot['groomer_id']);
        }
    }

    public function test_slot_start_is_aligned_to_interval(): void
    {
        $groomer = $this->buatGroomer('Groomer Interval');
        $this->buatShift($groomer, '09:10:00', '11:00:00');

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);
        $this->assertSame('2026-11-09 09:30:00', $hasil->slots[0]['mulai']);
    }

    public function test_booking_lead_time_setting_is_respected(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-11-09 08:00:00', 'Asia/Jakarta')
        );

        AppSetting::updateOrCreate(
            ['key' => 'batas_minimum_pesan'],
            ['value' => '90']
        );

        $groomer = $this->buatGroomer('Groomer Lead Time');
        $this->buatShift($groomer, '09:00:00', '11:00:00');

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertNotEmpty($hasil->slots);
        $this->assertSame('2026-11-09 09:30:00', $hasil->slots[0]['mulai']);
    }

    public function test_date_outside_booking_horizon_returns_empty_slots(): void
    {
        $groomer = $this->buatGroomer('Groomer Horizon');
        $this->buatShift($groomer);

        $hasil = $this->service->cari(
            '2026-11-23',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertEmpty($hasil->slots);
        $this->assertSame(
            'Tanggal berada di luar batas pemesanan.',
            $hasil->alasan
        );
    }

    public function test_search_does_not_create_or_modify_appointments(): void
    {
        $groomer = $this->buatGroomer('Groomer Read Only');
        $this->buatShift($groomer);

        $this->buatAppointment(
            $groomer,
            '2026-11-09 12:00:00',
            '2026-11-09 13:00:00',
            60
        );

        $jumlahSebelum = DB::table('appointments')->count();

        $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $jumlahSesudah = DB::table('appointments')->count();

        $this->assertSame($jumlahSebelum, $jumlahSesudah);
    }

    public function test_groomer_with_inactive_user_is_not_offered(): void
    {
        $groomer = $this->buatGroomer('Groomer Nonaktif');
        $this->buatShift($groomer);

        $groomer->user->update(['is_active' => false]);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertEmpty($hasil->slots);
        $this->assertSame(
            'Belum ada groomer yang sesuai dengan kebutuhan hewan.',
            $hasil->alasan
        );
    }

    public function test_slot_does_not_cross_break_between_shifts(): void
    {
        $groomer = $this->buatGroomer('Groomer Istirahat');

        $this->buatShift($groomer, '09:00:00', '12:00:00');
        $this->buatShift($groomer, '13:00:00', '17:00:00');

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $jamMulai = array_column($hasil->slots, 'mulai');

        $this->assertNotContains('2026-11-09 11:30:00', $jamMulai);
        $this->assertContains('2026-11-09 11:00:00', $jamMulai);
        $this->assertContains('2026-11-09 13:00:00', $jamMulai);
    }

    public function test_same_start_time_is_returned_only_once(): void
    {
        $groomerSatu = $this->buatGroomer('Groomer Dedup Satu');
        $groomerDua = $this->buatGroomer('Groomer Dedup Dua');

        $this->buatShift($groomerSatu);
        $this->buatShift($groomerDua);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $jamMulai = array_column($hasil->slots, 'mulai');

        $this->assertSame(
            count($jamMulai),
            count(array_unique($jamMulai))
        );
    }

    public function test_equal_workload_selects_groomer_with_smallest_id(): void
    {
        $groomerSatu = $this->buatGroomer('Groomer Tie Satu');
        $groomerDua = $this->buatGroomer('Groomer Tie Dua');

        $this->buatShift($groomerSatu);
        $this->buatShift($groomerDua);

        $hasilPertama = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $hasilKedua = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertSame(
            $groomerSatu->id,
            $hasilPertama->slots[0]['groomer_id']
        );

        $this->assertSame(
            $groomerSatu->id,
            $hasilKedua->slots[0]['groomer_id']
        );

        $this->assertLessThan($groomerDua->id, $groomerSatu->id);
    }

    public function test_no_shift_returns_clear_reason(): void
    {
        $this->buatGroomer('Groomer Tanpa Shift');

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertEmpty($hasil->slots);
        $this->assertSame(
            'Tidak ada groomer yang memiliki shift pada tanggal tersebut.',
            $hasil->alasan
        );
    }

    public function test_no_suitable_groomer_returns_clear_reason(): void
    {
        $this->pet->update(['perilaku' => 'agresif']);

        $groomer = $this->buatGroomer(
            'Groomer Tidak Cocok',
            'dasar',
            false,
            false
        );

        $this->buatShift($groomer);

        $hasil = $this->service->cari(
            '2026-11-09',
            60,
            $this->pet,
            [$this->layanan]
        );

        $this->assertEmpty($hasil->slots);
        $this->assertSame(
            'Belum ada groomer yang sesuai dengan kebutuhan hewan.',
            $hasil->alasan
        );
    }
}