<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingStatusTest extends TestCase
{
    use RefreshDatabase;

    private function buatBooking(): Appointment
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Pengujian',
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
            'name' => 'Groomer Rahasia',
        ]);

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $appointment = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->addDay(),
            'selesai_estimasi' => now()->addDay()->addMinutes(60),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => str_repeat('A', 64),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
            'catatan_pelanggan' => 'Jangan terlalu pendek.',
        ]);

        AppointmentService::create([
            'appointment_id' => $appointment->id,
            'service_id' => null,
            'nama_layanan' => 'Full Grooming',
            'durasi_dasar' => 60,
            'harga' => 100000,
        ]);

        return $appointment;
    }

    public function test_valid_token_displays_booking_status_page(): void
{
    $appointment = $this->buatBooking();

    $this->get('/status/'.$appointment->token)
        ->assertOk()
        ->assertViewIs('booking.status')
        ->assertViewHas('token', $appointment->token)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
}

    public function test_valid_token_displays_booking_status(): void
    {
        $appointment = $this->buatBooking();

        $response = $this->getJson('/status/'.$appointment->token.'/data');

        $response
            ->assertOk()
            ->assertJsonPath('booking.pet', 'Milo')
            ->assertJsonPath('booking.status', 'menunggu')
            ->assertJsonPath('booking.layanan.0.nama', 'Full Grooming')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_invalid_token_returns_same_not_found_response(): void
    {
        $response = $this->getJson('/status/'.str_repeat('X', 64).'/data');

        $response
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Link tidak valid.',
            ]);
    }

    public function test_short_token_also_returns_not_found(): void
    {
        $response = $this->getJson('/status/abc/data');

        $response
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Link tidak valid.',
            ]);
    }

    public function test_public_status_does_not_expose_private_information(): void
    {
        $appointment = $this->buatBooking();

        $response = $this->getJson('/status/'.$appointment->token.'/data');

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringNotContainsString(
            '6281234567890',
            $content
        );

        $this->assertStringNotContainsString(
            'Pelanggan Pengujian',
            $content
        );

        $this->assertStringNotContainsString(
            'Groomer Rahasia',
            $content
        );

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_booking_token_has_at_least_40_characters(): void
    {
        $appointment = $this->buatBooking();

        $this->assertGreaterThanOrEqual(
            40,
            strlen($appointment->token)
        );
    }

    public function test_each_booking_has_a_unique_token(): void
    {
        $bookingPertama = $this->buatBooking();

        // Buat booking kedua dengan data pet dan groomer berbeda.
        $customer = Customer::create([
            'nama' => 'Pelanggan Kedua',
            'no_wa' => '6289876543210',
        ]);

        $pet = Pet::create([
            'customer_id' => $customer->id,
            'nama' => 'Luna',
            'spesies' => 'kucing',
            'ras' => 'Persia',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);

        $user = User::factory()->create([
            'name' => 'Groomer Kedua',
        ]);

        $groomer = Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'senior',
            'bisa_kucing' => true,
            'bisa_agresif' => true,
        ]);

        $bookingKedua = Appointment::create([
            'pet_id' => $pet->id,
            'groomer_id' => $groomer->id,
            'mulai' => now()->addDays(2),
            'selesai_estimasi' => now()->addDays(2)->addMinutes(60),
            'durasi_estimasi' => 60,
            'status' => 'menunggu',
            'token' => Str::random(64),
            'rincian_estimasi' => [],
            'sumber' => 'publik',
        ]);

        $this->assertNotSame(
            $bookingPertama->token,
            $bookingKedua->token
        );
    }

    public function test_public_status_reflects_booking_status_changes(): void
    {
        $appointment = $this->buatBooking();

        foreach (['dikerjakan', 'selesai', 'dijemput', 'dibatalkan'] as $status) {
            $appointment->update(['status' => $status]);

            $this->getJson('/status/'.$appointment->token.'/data')
                ->assertOk()
                ->assertJsonPath('booking.status', $status);
        }
    }

    public function test_cancelled_booking_is_still_visible_in_public_status(): void
    {
        $appointment = $this->buatBooking();

        $appointment->update(['status' => 'dibatalkan']);

        $this->getJson('/status/'.$appointment->token.'/data')
            ->assertOk()
            ->assertJsonPath('booking.status', 'dibatalkan');
    }

    public function test_status_endpoint_is_throttled_after_30_requests(): void
    {
        $appointment = $this->buatBooking();

        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/status/'.$appointment->token.'/data')
                ->assertOk();
        }

        $this->getJson('/status/'.$appointment->token.'/data')
            ->assertStatus(429);
    }
}
