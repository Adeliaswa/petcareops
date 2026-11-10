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

            ['key' => 'skill_minimum_kecil', 'value' => 'dasar'],
            ['key' => 'skill_minimum_sedang', 'value' => 'menengah'],
            ['key' => 'skill_minimum_besar', 'value' => 'menengah'],

            // Pengaturan informasi landing page M4
            ['key' => 'jam_buka', 'value' => '08:00-17:00'],
            ['key' => 'kontak_wa', 'value' => ''],
            ['key' => 'alamat', 'value' => ''],

            ['key' => 'jarak_pengingat_hari', 'value' => '28'],
            ['key' => 'nama_salon', 'value' => 'PetCareOps'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}