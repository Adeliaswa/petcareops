<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_fonnte_token_returns_wa_fallback(): void
    {
        config(['services.fonnte.token' => null]);

        $hasil = app(WhatsAppService::class)->kirim(
            'konfirmasi_booking',
            '081234567890',
            'Booking berhasil.'
        );

        $this->assertSame('fallback', $hasil['status']);
        $this->assertStringContainsString(
            'https://wa.me/6281234567890',
            $hasil['wa_link']
        );

        $this->assertDatabaseHas('notification_logs', [
            'jenis' => 'konfirmasi_booking',
            'nomor_tujuan' => '6281234567890',
            'status' => 'fallback',
        ]);
    }

    public function test_successful_fonnte_delivery_is_logged(): void
    {
        config([
            'services.fonnte.token' => 'fake-token',
        ]);

        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => true,
                'detail' => 'success',
            ], 200),
        ]);

        $hasil = app(WhatsAppService::class)->kirim(
    'konfirmasi_booking',
    '081234567890',
    'Booking berhasil.'
);

        $this->assertSame('berhasil', $hasil['status']);
        $this->assertNull($hasil['wa_link']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'fake-token')
                && $request['target'] === '6281234567890'
                && $request['message'] === 'Booking berhasil.';
        });

        $this->assertDatabaseHas('notification_logs', [
            'jenis' => 'konfirmasi_booking',
            'nomor_tujuan' => '6281234567890',
            'status' => 'berhasil',
        ]);
    }

    public function test_fonnte_failure_returns_wa_fallback(): void
    {
        config([
            'services.fonnte.token' => 'fake-token',
        ]);

        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => false,
                'detail' => 'Failed',
            ], 500),
        ]);

        $hasil = app(WhatsAppService::class)->kirim(
            'konfirmasi_booking',
            '081234567890',
            'Booking berhasil.'
        );

        $this->assertSame('fallback', $hasil['status']);
        $this->assertNotNull($hasil['wa_link']);

        $this->assertDatabaseHas('notification_logs', [
            'jenis' => 'konfirmasi_booking',
            'status' => 'fallback',
        ]);
    }
}
