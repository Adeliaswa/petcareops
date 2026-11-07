<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duration_factors', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 20);
            $table->string('key', 50);
            $table->string('label');
            $table->decimal('multiplier', 8, 3);
            $table->unsignedInteger('extra_minutes')->default(0);
            $table->timestamps();

            $table->unique(['kategori', 'key']);
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duration_factors');
    }
};