<?php

namespace Tests\Unit;

use App\Services\PhoneNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    public function test_normalizes_indonesian_phone_number_formats(): void
    {
        $normalizer = new PhoneNormalizer();

        $this->assertSame(
            '6281234567890',
            $normalizer->normalize('0812 3456 7890')
        );

        $this->assertSame(
            '6281234567890',
            $normalizer->normalize('+62 812-3456-7890')
        );

        $this->assertSame(
            '6281234567890',
            $normalizer->normalize('6281234567890')
        );

        $this->assertSame(
            '6281234567890',
            $normalizer->normalize('81234567890')
        );
    }

    public function test_rejects_invalid_phone_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Format nomor WhatsApp tidak valid.'
        );

        (new PhoneNormalizer())->normalize('12345');
    }
}