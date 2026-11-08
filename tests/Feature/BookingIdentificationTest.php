<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingIdentificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_customer_can_be_identified_by_whatsapp(): void
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Contoh',
            'no_wa' => '6281234567890',
        ]);

        Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'kucing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        $response = $this->postJson('/booking/identifikasi', [
            'no_wa' => '081234567890',
        ]);

        $response->assertOk()
            ->assertJson([
                'dikenal' => true,
                'pets' => [
                    [
                        'id' => $customer->pets()->first()->id,
                        'nama' => 'Milo',
                    ],
                ],
            ])
            ->assertJsonMissingPath('nama')
            ->assertJsonMissingPath('no_wa')
            ->assertJsonMissingPath('pets.0.spesies')
            ->assertJsonMissingPath('pets.0.ras')
            ->assertJsonMissingPath('pets.0.alergi')
            ->assertJsonMissingPath('pets.0.catatan_kesehatan');
    }

    public function test_unknown_customer_is_not_saved_during_identification(): void
    {
        $response = $this->postJson('/booking/identifikasi', [
            'no_wa' => '081234567890',
        ]);

        $response->assertOk()
            ->assertExactJson([
                'dikenal' => false,
                'pets' => [],
            ]);

        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('pets', 0);
    }

    public function test_invalid_whatsapp_number_is_rejected(): void
    {
        $response = $this->postJson('/booking/identifikasi', [
            'no_wa' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Format nomor WhatsApp tidak valid.',
            ]);
    }

    public function test_identifikasi_whatsapp_dibatasi_30_request_per_menit(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/booking/identifikasi', [
                'no_wa' => '081234567890',
            ])->assertOk();
        }

        $this->postJson('/booking/identifikasi', [
            'no_wa' => '081234567890',
        ])->assertTooManyRequests();
    }

    public function test_variasi_format_whatsapp_dikenali_sebagai_pelanggan_yang_sama(): void
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Contoh',
            'no_wa' => '6281234567890',
        ]);

        Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Milo',
            'spesies' => 'kucing',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'pendek',
            'perilaku' => 'normal',
        ]);

        foreach ([
            '081234567890',
            '+6281234567890',
            '6281234567890',
        ] as $nomor) {
            $this->postJson('/booking/identifikasi', [
                'no_wa' => $nomor,
            ])
                ->assertOk()
                ->assertJsonPath('dikenal', true)
                ->assertJsonPath('pets.0.nama', 'Milo');
        }

        $this->assertDatabaseCount('customers', 1);
    }
}
