<?php

namespace Tests\Unit;

use App\Exceptions\BusinessRuleException;
use App\Models\Customer;
use App\Models\DurationFactor;
use App\Models\Pet;
use App\Models\Service;
use App\Services\DurationCalculator;
use Database\Seeders\DurationFactorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DurationCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected DurationCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DurationFactorSeeder::class);

        $this->calculator = app(DurationCalculator::class);
    }

    private function createPet(
        string $ukuran = 'kecil',
        string $jenisBulu = 'pendek',
        string $perilaku = 'normal'
    ): Pet {
        $customer = Customer::create([
            'nama' => 'Pemilik Test',
            'no_wa' => '6281234567890',
        ]);

        return Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ukuran' => $ukuran,
            'jenis_bulu' => $jenisBulu,
            'perilaku' => $perilaku,
        ]);
    }

    private function createService(
        string $nama = 'Grooming',
        int $durasi = 45,
        bool $aktif = true
    ): Service {
        return Service::create([
            'nama' => $nama,
            'durasi_dasar' => $durasi,
            'harga' => 75000,
            'aktif' => $aktif,
        ]);
    }

    public function test_small_dog_short_fur_estimation_is_45_minutes(): void
    {
        $pet = $this->createPet();
        $service = $this->createService('Grooming', 30);
        $serviceTambahan = $this->createService('Potong Kuku', 15);

        $hasil = $this->calculator->hitung(
            $pet,
            [$service, $serviceTambahan]
        );

        $this->assertSame(45, $hasil->menit_total);
        $this->assertSame(45, $hasil->rincian['total_durasi_dasar']);
    }

    public function test_golden_retriever_with_matted_fur_estimation_is_120_minutes(): void
    {
        $pet = $this->createPet(
            ukuran: 'besar',
            jenisBulu: 'panjang',
            perilaku: 'normal'
        );

        $service = $this->createService('Grooming', 45);

        $hasil = $this->calculator->hitung(
            $pet,
            [$service],
            ['kusut']
        );

        $this->assertSame(120, $hasil->menit_total);
        $this->assertEquals(
            119.5,
            $hasil->rincian['durasi_sebelum_pembulatan']
        );
        $this->assertSame(15, $hasil->rincian['total_menit_tambahan']);
        $this->assertSame(10, $hasil->rincian['buffer_pembersihan']);
    }

    public function test_calculation_uses_updated_factor_from_database(): void
    {
        $pet = $this->createPet();

        DurationFactor::where('kategori', 'ukuran')
            ->where('key', 'kecil')
            ->update(['multiplier' => 2.0]);

        $service = $this->createService('Grooming', 45);

        $hasil = $this->calculator->hitung($pet, [$service]);

        $this->assertSame(90, $hasil->menit_total);
    }

    public function test_multiple_services_are_summed_before_multiplication(): void
    {
        $pet = $this->createPet(
            ukuran: 'sedang',
            jenisBulu: 'pendek'
        );

        $service1 = $this->createService('Mandi', 30);
        $service2 = $this->createService('Potong Kuku', 15);

        $hasil = $this->calculator->hitung(
            $pet,
            [$service1, $service2]
        );

        $this->assertSame(65, $hasil->menit_total);
        $this->assertEquals(
            64,
            $hasil->rincian['durasi_sebelum_pembulatan']
        );
        $this->assertSame(45, $hasil->rincian['total_durasi_dasar']);
    }

    public function test_multiple_extra_conditions_are_summed(): void
    {
        $pet = $this->createPet();

        $service = $this->createService('Grooming', 45);

        $hasil = $this->calculator->hitung(
            $pet,
            [$service],
            ['kusut', 'kutu', 'jamur']
        );

        $this->assertSame(110, $hasil->menit_total);
        $this->assertSame(45, $hasil->rincian['total_durasi_dasar']);
        $this->assertSame(65, $hasil->rincian['total_menit_tambahan']);
        $this->assertSame(0, $hasil->rincian['buffer_pembersihan']);
    }

    public function test_aggressive_behavior_multiplier_is_applied(): void
    {
        $pet = $this->createPet(perilaku: 'agresif');
        $service = $this->createService('Grooming', 45);

        $hasil = $this->calculator->hitung($pet, [$service]);

        $this->assertSame(55, $hasil->menit_total);
        $this->assertEquals(
            54,
            $hasil->rincian['durasi_sebelum_pembulatan']
        );
        $this->assertSame(
            1.2,
            $hasil->rincian['faktor']['perilaku']['multiplier']
        );
    }

    public function test_duration_is_rounded_up_to_next_multiple_of_five(): void
    {
        $pet = $this->createPet();
        $service = $this->createService('Grooming', 101);

        $hasil = $this->calculator->hitung($pet, [$service]);

        $this->assertSame(105, $hasil->menit_total);
    }

    public function test_exact_multiple_of_five_is_not_changed(): void
    {
        $pet = $this->createPet();
        $service = $this->createService('Grooming', 100);

        $hasil = $this->calculator->hitung($pet, [$service]);

        $this->assertSame(100, $hasil->menit_total);
    }

    public function test_missing_factor_throws_business_rule_exception(): void
    {
        $pet = $this->createPet();

        DurationFactor::where('kategori', 'ukuran')
            ->where('key', 'kecil')
            ->delete();

        $service = $this->createService();

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Faktor durasi');

        $this->calculator->hitung($pet, [$service]);
    }

    public function test_empty_service_list_is_rejected(): void
    {
        $pet = $this->createPet();

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Pilih minimal satu layanan');

        $this->calculator->hitung($pet, []);
    }

    public function test_inactive_service_is_rejected(): void
    {
        $pet = $this->createPet();

        $service = $this->createService(
            nama: 'Layanan Nonaktif',
            aktif: false
        );

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('sudah tidak aktif');

        $this->calculator->hitung($pet, [$service]);
    }

    public function test_result_contains_complete_calculation_breakdown(): void
    {
        $pet = $this->createPet();
        $service = $this->createService();

        $hasil = $this->calculator->hitung($pet, [$service]);

        $this->assertArrayHasKey('layanan', $hasil->rincian);
        $this->assertArrayHasKey('faktor', $hasil->rincian);
        $this->assertArrayHasKey('tambahan', $hasil->rincian);
        $this->assertArrayHasKey('buffer_pembersihan', $hasil->rincian);
        $this->assertArrayHasKey('durasi_sebelum_pembulatan', $hasil->rincian);
        $this->assertArrayHasKey('menit_total', $hasil->rincian);

        $this->assertSame(
            $hasil->menit_total,
            $hasil->rincian['menit_total']
        );
    }

    public function test_same_input_produces_same_estimation(): void
    {
        $pet = $this->createPet();
        $service = $this->createService();

        $hasilPertama = $this->calculator->hitung($pet, [$service]);
        $hasilKedua = $this->calculator->hitung($pet, [$service]);

        $this->assertSame(
            $hasilPertama->menit_total,
            $hasilKedua->menit_total
        );
    }
}