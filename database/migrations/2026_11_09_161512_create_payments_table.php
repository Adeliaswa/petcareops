<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->string('metode', 20);
            $table->unsignedBigInteger('nominal');
            $table->dateTime('dibayar_pada');

            $table->foreignId('dicatat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};