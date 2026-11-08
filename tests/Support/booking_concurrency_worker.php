<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/vendor/autoload.php';

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

$app = require $basePath . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[
    ,
    $petId,
    $groomerId,
    $mulai,
    $token,
    $directory,
    $workerId,
] = $argv;

$signal = function (string $name, string $content = '') use (
    $directory,
    $workerId
): void {
    file_put_contents(
        $directory . DIRECTORY_SEPARATOR . $workerId . '.' . $name,
        $content,
        LOCK_EX
    );
};

$signal('ready');

$deadline = microtime(true) + 20;

while (!file_exists($directory . DIRECTORY_SEPARATOR . 'go')) {
    if (microtime(true) > $deadline) {
        exit(3);
    }

    usleep(10000);
}

$connection = DB::connection();
$connection->beginTransaction();

try {
    $signal('attempting');

    DB::table('appointments')->insert([
        'pet_id' => (int) $petId,
        'groomer_id' => (int) $groomerId,
        'mulai' => $mulai,
        'selesai_estimasi' => date(
            'Y-m-d H:i:s',
            strtotime($mulai . ' +60 minutes')
        ),
        'durasi_estimasi' => 60,
        'status' => 'menunggu',
        'token' => $token,
        'rincian_estimasi' => json_encode([]),
        'sumber' => 'publik',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $signal('inserted');

    $deadline = microtime(true) + 20;

    while (!file_exists($directory . DIRECTORY_SEPARATOR . 'commit')) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('Timeout menunggu izin commit.');
        }

        usleep(10000);
    }

    $connection->commit();
    $signal('success');
} catch (QueryException $e) {
    if ($connection->transactionLevel() > 0) {
        $connection->rollBack();
    }

    $signal('rejected', $e->errorInfo[0] ?? 'unknown');

    if (($e->errorInfo[0] ?? null) !== '23P01') {
        exit(2);
    }
} catch (Throwable $e) {
    if ($connection->transactionLevel() > 0) {
        $connection->rollBack();
    }

    $signal('error', $e->getMessage());
    exit(2);
}