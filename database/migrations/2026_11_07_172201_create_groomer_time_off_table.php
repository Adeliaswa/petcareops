<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groomer_time_off', function (Blueprint $table) {
            $table->id();

            $table->foreignId('groomer_id')
                ->constrained('groomers')
                ->restrictOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('alasan')->nullable();

            $table->timestamps();

            $table->index(['groomer_id', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groomer_time_off');
    }
};