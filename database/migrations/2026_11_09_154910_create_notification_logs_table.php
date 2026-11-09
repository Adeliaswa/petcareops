
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 50);
            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('reminder_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('nomor_tujuan', 30);
            $table->text('pesan');
            $table->string('status', 20);
            $table->text('respons')->nullable();
            $table->timestamp('terkirim_at')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
