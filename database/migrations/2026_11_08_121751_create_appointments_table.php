<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pet_id')
                ->constrained('pets')
                ->restrictOnDelete();

            $table->foreignId('groomer_id')
                ->constrained('groomers')
                ->restrictOnDelete();

            $table->dateTime('mulai');
            $table->dateTime('selesai_estimasi');
            $table->unsignedInteger('durasi_estimasi');

            $table->string('status', 30)->default('menunggu');

            $table->string('token', 64)->unique();
            $table->json('rincian_estimasi');
            $table->string('sumber', 20)->default('publik');
            $table->text('catatan_pelanggan')->nullable();

            $table->timestamps();

            $table->index(['groomer_id', 'mulai']);
            $table->index(['pet_id', 'mulai']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};