<?php

namespace Database\Seeders;

use App\Models\DurationFactor;
use Illuminate\Database\Seeder;

class DurationFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            // Ukuran hewan
            ['kategori' => 'ukuran', 'key' => 'kecil', 'label' => 'Kecil', 'multiplier' => 1.0, 'extra_minutes' => 0],
            ['kategori' => 'ukuran', 'key' => 'sedang', 'label' => 'Sedang', 'multiplier' => 1.2, 'extra_minutes' => 0],
            ['kategori' => 'ukuran', 'key' => 'besar', 'label' => 'Besar', 'multiplier' => 1.5, 'extra_minutes' => 0],

            // Jenis bulu
            ['kategori' => 'jenis_bulu', 'key' => 'pendek', 'label' => 'Pendek', 'multiplier' => 1.0, 'extra_minutes' => 0],
            ['kategori' => 'jenis_bulu', 'key' => 'sedang', 'label' => 'Sedang', 'multiplier' => 1.2, 'extra_minutes' => 0],
            ['kategori' => 'jenis_bulu', 'key' => 'panjang', 'label' => 'Panjang', 'multiplier' => 1.4, 'extra_minutes' => 0],

            // Perilaku
            ['kategori' => 'perilaku', 'key' => 'normal', 'label' => 'Normal', 'multiplier' => 1.0, 'extra_minutes' => 0],
            ['kategori' => 'perilaku', 'key' => 'agresif', 'label' => 'Agresif', 'multiplier' => 1.2, 'extra_minutes' => 0],

            // Kondisi tambahan
            ['kategori' => 'kondisi', 'key' => 'kusut', 'label' => 'Bulu kusut', 'multiplier' => 1.0, 'extra_minutes' => 15],
            ['kategori' => 'kondisi', 'key' => 'kutu', 'label' => 'Kutu', 'multiplier' => 1.0, 'extra_minutes' => 20],
            ['kategori' => 'kondisi', 'key' => 'jamur', 'label' => 'Jamur', 'multiplier' => 1.0, 'extra_minutes' => 30],

            // Buffer pembersihan
            ['kategori' => 'buffer', 'key' => 'kecil', 'label' => 'Buffer hewan kecil', 'multiplier' => 1.0, 'extra_minutes' => 0],
            ['kategori' => 'buffer', 'key' => 'sedang', 'label' => 'Buffer hewan sedang', 'multiplier' => 1.0, 'extra_minutes' => 10],
            ['kategori' => 'buffer', 'key' => 'besar', 'label' => 'Buffer hewan besar', 'multiplier' => 1.0, 'extra_minutes' => 10],
        ];

        foreach ($factors as $factor) {
            DurationFactor::updateOrCreate(
                [
                    'kategori' => $factor['kategori'],
                    'key' => $factor['key'],
                ],
                $factor
            );
        }
    }
}