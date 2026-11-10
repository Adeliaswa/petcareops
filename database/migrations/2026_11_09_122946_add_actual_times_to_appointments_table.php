<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('waktu_mulai_aktual')->nullable();
            $table->timestamp('waktu_selesai_aktual')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'waktu_mulai_aktual',
                'waktu_selesai_aktual',
            ]);
        });
    }
};