<?php

namespace Database\Seeders;

use App\Models\Groomer;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroomerSeeder extends Seeder
{
    public function run(): void
    {
        $groomers = User::role('groomer')->get();

        foreach ($groomers as $user) {
            Groomer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'level_skill' => 'dasar',
                    'bisa_kucing' => false,
                    'bisa_agresif' => false,
                ]
            );
        }
    }
}