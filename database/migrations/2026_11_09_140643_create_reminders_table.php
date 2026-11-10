<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pet_id')
                ->constrained('pets')
                ->cascadeOnDelete();

            $table->foreignId('servis_terakhir_id')
                ->constrained('appointments')
                ->cascadeOnDelete();

            $table->date('tanggal_jatuh_tempo');

            $table->string('status', 20)
                ->default('menunggu');

            $table->timestamp('terkirim_at')->nullable();

            $table->timestamp('ditutup_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['pet_id', 'servis_terakhir_id'],
                'reminders_pet_servis_unique'
            );

            $table->index(['status', 'tanggal_jatuh_tempo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
