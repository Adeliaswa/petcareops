<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountService
{
    private const ALLOWED_ROLES = [
        'admin',
        'resepsionis',
        'groomer',
    ];

    public function buat(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roleName = $data['role'];

            if (!in_array($roleName, self::ALLOWED_ROLES, true)) {
                throw ValidationException::withMessages([
                    'role' => 'Role yang dipilih tidak valid.',
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $user->syncRoles([$roleName]);

            return $user;
        });
    }

    public function nonaktifkan(User $user, User $admin): void
    {
        DB::transaction(function () use ($user, $admin) {
            if ($user->id === $admin->id) {
                throw ValidationException::withMessages([
                    'account' => 'Admin tidak dapat menonaktifkan akunnya sendiri.',
                ]);
            }

            if (!$user->is_active) {
                return;
            }

            if ($user->hasRole('admin')) {
                $activeAdmins = User::role('admin')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['users.id'])
                    ->count();

                if ($activeAdmins <= 1) {
                    throw ValidationException::withMessages([
                        'account' => 'Minimal harus ada satu admin aktif.',
                    ]);
                }
            }

            $user->update([
                'is_active' => false,
            ]);
        });
    }

    public function resetPassword(User $user): string
    {
        $temporaryPassword = Str::random(12);

        $user->update([
            'password' => $temporaryPassword,
        ]);

        return $temporaryPassword;
    }

    public function aktifkan(User $user): void
    {
        $user->update([
            'is_active' => true,
        ]);
    }
}