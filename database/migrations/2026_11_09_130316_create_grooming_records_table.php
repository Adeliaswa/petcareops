<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')
                ->unique()
                ->constrained('appointments')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('durasi_aktual');
            $table->text('catatan_grooming');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_records');
    }
};