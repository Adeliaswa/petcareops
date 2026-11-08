<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Constraint ini menggunakan fitur khusus PostgreSQL.
        // SQLite melewati bagian ini agar test fitur biasa tetap berjalan.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Dibutuhkan agar tipe integer dapat digunakan dalam GiST exclusion constraint.
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        // Satu groomer tidak boleh menangani dua appointment aktif
        // pada rentang waktu yang saling bertumpuk.
        DB::statement("
            ALTER TABLE appointments
            ADD CONSTRAINT appointments_groomer_no_overlap
            EXCLUDE USING gist (
                groomer_id WITH =,
                tsrange(mulai, selesai_estimasi, '[)') WITH &&
            )
            WHERE (status <> 'dibatalkan')
        ");

        // Satu pet juga tidak boleh memiliki dua appointment aktif
        // pada rentang waktu yang saling bertumpuk.
        DB::statement("
            ALTER TABLE appointments
            ADD CONSTRAINT appointments_pet_no_overlap
            EXCLUDE USING gist (
                pet_id WITH =,
                tsrange(mulai, selesai_estimasi, '[)') WITH &&
            )
            WHERE (status <> 'dibatalkan')
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE appointments
            DROP CONSTRAINT IF EXISTS appointments_groomer_no_overlap
        ");

        DB::statement("
            ALTER TABLE appointments
            DROP CONSTRAINT IF EXISTS appointments_pet_no_overlap
        ");
    }
};