<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'app_settings',
        'appointment_services',
        'appointments',
        'cache',
        'cache_locks',
        'customers',
        'duration_factors',
        'failed_jobs',
        'groomer_shifts',
        'groomer_time_off',
        'groomers',
        'grooming_records',
        'invoices',
        'job_batches',
        'jobs',
        'migrations',
        'model_has_permissions',
        'model_has_roles',
        'notification_logs',
        'password_reset_tokens',
        'payments',
        'permissions',
        'pets',
        'reminders',
        'role_has_permissions',
        'roles',
        'services',
        'sessions',
        'users',
    ];

    public function up(): void
    {
        DB::statement('SET ROLE postgres');

        try {
            foreach ($this->tables as $table) {
                DB::statement(
                    'ALTER TABLE public."' . $table . '" ENABLE ROW LEVEL SECURITY'
                );
            }
        } finally {
            DB::statement('RESET ROLE');
        }
    }

    public function down(): void
    {
        DB::statement('SET ROLE postgres');

        try {
            foreach (array_reverse($this->tables) as $table) {
                DB::statement(
                    'ALTER TABLE public."' . $table . '" DISABLE ROW LEVEL SECURITY'
                );
            }
        } finally {
            DB::statement('RESET ROLE');
        }
    }
};