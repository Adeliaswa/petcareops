<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Admin PetCareOps',
                'email' => 'admin@petcareops.test',
                'role' => 'admin',
                'password_key' => 'admin',
            ],
            [
                'name' => 'Receptionist PetCareOps',
                'email' => 'receptionist@petcareops.test',
                'role' => 'resepsionis',
                'password_key' => 'receptionist',
            ],
            [
                'name' => 'Groomer 1',
                'email' => 'groomer1@petcareops.test',
                'role' => 'groomer',
                'password_key' => 'groomer1',
            ],
            [
                'name' => 'Groomer 2',
                'email' => 'groomer2@petcareops.test',
                'role' => 'groomer',
                'password_key' => 'groomer2',
            ],
            [
                'name' => 'Groomer 3',
                'email' => 'groomer3@petcareops.test',
                'role' => 'groomer',
                'password_key' => 'groomer3',
            ],
        ];

        foreach ($accounts as $account) {
            $password = config(
                'petcareops.seed_passwords.' . $account['password_key']
            );

            if (empty($password)) {
                throw new RuntimeException(
                    'Password untuk ' . $account['email'] .
                    ' belum diatur di file .env.'
                );
            }

            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$account['role']]);
        }
    }
}