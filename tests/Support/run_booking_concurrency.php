<?php

use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/vendor/autoload.php';

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

$app = require $basePath . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDriverName() !== 'pgsql') {
    fwrite(STDERR, "Pengujian ini wajib menggunakan PostgreSQL.\n");
    exit(1);
}

$directory = sys_get_temp_dir()
    . DIRECTORY_SEPARATOR
    . 'petcareops-concurrency-'
    . Str::random(12);

mkdir($directory, 0777, true);

$processes = [];
$userIds = [];
$groomerIds = [];
$petIds = [];
$customerId = null;

try {
    $customer = Customer::create([
        'nama' => 'Pelanggan Uji Konkurensi',
        'no_wa' => '628' . random_int(100000000, 999999999),
    ]);

    $customerId = $customer->id;

    $pets = [];

    foreach (['Milo', 'Bobby'] as $nama) {
        $pets[] = Pet::create([
            'customer_id' => $customer->id,
            'nama' => $nama,
            'spesies' => 'anjing',
            'ras' => 'Poodle',
            'ukuran' => 'kecil',
            'jenis_bulu' => 'panjang',
            'perilaku' => 'normal',
        ]);
    }

    $petIds = array_map(fn ($pet) => $pet->id, $pets);

    $user = User::factory()->create();
    $userIds[] = $user->id;

    $groomer = Groomer::create([
        'user_id' => $user->id,
        'level_skill' => 'ahli',
        'bisa_kucing' => true,
        'bisa_agresif' => true,
    ]);

    $groomerIds[] = $groomer->id;

    $mulai = now('Asia/Jakarta')
        ->addDays(2)
        ->startOfDay()
        ->addHours(10)
        ->format('Y-m-d H:i:s');

    $workerPath = $basePath
        . DIRECTORY_SEPARATOR
        . 'tests'
        . DIRECTORY_SEPARATOR
        . 'Support'
        . DIRECTORY_SEPARATOR
        . 'booking_concurrency_worker.php';

    $tokens = [Str::random(64), Str::random(64)];

    foreach ([0, 1] as $i) {
        $command = implode(' ', array_map('escapeshellarg', [
            PHP_BINARY,
            $workerPath,
            (string) $petIds[$i],
            (string) $groomer->id,
            $mulai,
            $tokens[$i],
            $directory,
            (string) $i,
        ]));

        $processes[$i] = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['file', "$directory/$i.out", 'w'],
                2 => ['file', "$directory/$i.err", 'w'],
            ],
            $pipes,
            $basePath
        );

        if (!is_resource($processes[$i])) {
            throw new RuntimeException("Worker $i gagal dijalankan.");
        }

        fclose($pipes[0]);
    }

    $waitFor = function (callable $condition, string $message): void {
        $deadline = microtime(true) + 20;

        while (!$condition()) {
            if (microtime(true) > $deadline) {
                throw new RuntimeException($message);
            }

            usleep(10000);
        }
    };

    $waitFor(
        fn () => file_exists("$directory/0.ready")
            && file_exists("$directory/1.ready"),
        'Worker tidak siap.'
    );

    touch("$directory/go");

    $waitFor(
        fn () => file_exists("$directory/0.attempting")
            && file_exists("$directory/1.attempting"),
        'Worker tidak mulai mencoba booking.'
    );

    $waitFor(
        fn () => file_exists("$directory/0.inserted")
            || file_exists("$directory/1.inserted"),
        'Tidak ada worker yang berhasil memasukkan booking.'
    );

    // Izinkan transaksi pertama commit.
    // Transaksi kedua akan ditolak oleh exclusion constraint.
    touch("$directory/commit");

    $waitFor(
        fn () => (
            file_exists("$directory/0.success")
            || file_exists("$directory/0.rejected")
        ) && (
            file_exists("$directory/1.success")
            || file_exists("$directory/1.rejected")
        ),
        'Worker tidak menyelesaikan transaksi.'
    );

    $exitCodes = [];

    foreach ($processes as $i => $process) {
        $exitCodes[$i] = proc_close($process);
    }

    $processes = [];

    $successCount = count(glob("$directory/*.success"));
    $rejectedCount = count(glob("$directory/*.rejected"));

    $appointmentCount = DB::table('appointments')
        ->whereIn('token', $tokens)
        ->count();

    echo "Worker berhasil: $successCount\n";
    echo "Worker ditolak: $rejectedCount\n";
    echo "Booking tersimpan: $appointmentCount\n";

    if (
        $successCount !== 1
        || $rejectedCount !== 1
        || $appointmentCount !== 1
        || in_array(2, $exitCodes, true)
        || in_array(3, $exitCodes, true)
    ) {
        throw new RuntimeException(
            'Pengujian konkurensi gagal.'
        );
    }

    echo "PASS: hanya satu booking berhasil pada slot yang sama.\n";
} finally {
    foreach ($processes as $process) {
        if (is_resource($process)) {
            proc_terminate($process);
            proc_close($process);
        }
    }

    if ($customerId !== null) {
        DB::table('appointments')
            ->where('token', '!=', '')
            ->whereIn('token', $tokens ?? [])
            ->delete();

        DB::table('pets')->whereIn('id', $petIds)->delete();
        DB::table('groomers')->whereIn('id', $groomerIds)->delete();
        DB::table('users')->whereIn('id', $userIds)->delete();
        DB::table('customers')->where('id', $customerId)->delete();
    }

    foreach (glob("$directory/*") ?: [] as $file) {
        @unlink($file);
    }

    @rmdir($directory);
}