<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->string('nama');
            $table->string('spesies', 10);
            $table->string('ras')->nullable();
            $table->string('ukuran', 30);
            $table->string('jenis_bulu', 50);
            $table->string('perilaku', 30)->default('normal');
            $table->text('alergi')->nullable();
            $table->text('catatan_kesehatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['spesies', 'ukuran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};