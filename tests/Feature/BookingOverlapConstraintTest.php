<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingOverlapConstraintTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanData(): array
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Tes',
            'no_wa' => '6281234567890',
        ]);

        $petPertama = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $petKedua = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Bobby',
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $groomerPertama = Groomer::create([
            'user_id' => User::factory()->create()->id,
            'level_skill' => 'ahli',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $groomerKedua = Groomer::create([
            'user_id' => User::factory()->create()->id,
            'level_skill' => 'ahli',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        return compact(
            'petPertama',
            'petKedua',
            'groomerPertama',
            'groomerKedua'
        );
    }

    private function buatAppointment(
        int $petId,
        int $groomerId,
        string $mulai,
        string $token
    ): Appointment {
        $waktuMulai = now()->parse($mulai);

        return Appointment::create([
            'pet_id' => $petId,
            'groomer_id' => $groomerId,
            'mulai' => $waktuMulai,
            'selesai_estimasi' => $waktuMulai->copy()->addHour(),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => $token,
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped(
                'Tes constraint overlap hanya dijalankan pada PostgreSQL.'
            );
        }
    }

    private function pastikanOverlapDitolak(callable $callback): void
    {
        try {
            DB::transaction($callback);

            $this->fail('Database seharusnya menolak jadwal yang bertumpukan.');
        } catch (QueryException $e) {
            $this->assertSame('23P01', $e->errorInfo[0] ?? null);
        }
    }

    public function test_database_menolak_overlap_pada_groomer_yang_sama(): void
    {
        $data = $this->siapkanData();

        $mulai = now()->addDays(2)->startOfDay()->addHours(10)
            ->format('Y-m-d H:i:s');

        $this->buatAppointment(
            $data['petPertama']->id,
            $data['groomerPertama']->id,
            $mulai,
            Str::random(64)
        );

        $this->pastikanOverlapDitolak(function () use ($data, $mulai) {
            $this->buatAppointment(
                $data['petKedua']->id,
                $data['groomerPertama']->id,
                $mulai,
                Str::random(64)
            );
        });

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_database_menolak_overlap_pada_hewan_yang_sama(): void
    {
        $data = $this->siapkanData();

        $mulai = now()->addDays(2)->startOfDay()->addHours(10)
            ->format('Y-m-d H:i:s');

        $this->buatAppointment(
            $data['petPertama']->id,
            $data['groomerPertama']->id,
            $mulai,
            Str::random(64)
        );

        $this->pastikanOverlapDitolak(function () use ($data, $mulai) {
            $this->buatAppointment(
                $data['petPertama']->id,
                $data['groomerKedua']->id,
                $mulai,
                Str::random(64)
            );
        });

        $this->assertDatabaseCount('appointments', 1);
    }
}