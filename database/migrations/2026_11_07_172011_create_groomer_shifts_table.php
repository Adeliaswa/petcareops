<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groomer_shifts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('groomer_id')
                ->constrained('groomers')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            $table->timestamps();

            $table->index(['groomer_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groomer_shifts');
    }
};