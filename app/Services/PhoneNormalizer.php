<?php

namespace App\Services;

use InvalidArgumentException;

class PhoneNormalizer
{
    public function normalize(string $phone): string
    {
        $phone = preg_replace('/[\s\-().]/', '', trim($phone));

        if (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        } elseif (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        if (!preg_match('/^62[0-9]{8,13}$/', $phone)) {
            throw new InvalidArgumentException(
                'Format nomor WhatsApp tidak valid.'
            );
        }

        return $phone;
    }
}