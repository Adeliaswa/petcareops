<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class JalankanPengingat extends Command
{
    protected $signature = 'pengingat:jalankan';

    protected $description = 'Membuat pengingat grooming yang sudah jatuh tempo';

    public function handle(ReminderService $reminderService): int
    {
        $jumlahDibuat = $reminderService->generateDueReminders();

        $this->info("Proses pengingat selesai. {$jumlahDibuat} pengingat baru dibuat.");

        return self::SUCCESS;
    }
}