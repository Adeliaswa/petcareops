<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'slot_interval', 'value' => '30'],
            ['key' => 'batas_minimum_pesan', 'value' => '60'],
            ['key' => 'horizon_hari', 'value' => '14'],

            // Asumsi minimum skill berdasarkan ukuran hewan
            ['key' => 'skill_minimum_kecil', 'value' => 'dasar'],
            ['key' => 'skill_minimum_sedang', 'value' => 'menengah'],
            ['key' => 'skill_minimum_besar', 'value' => 'menengah'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}