<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\GroomingRecord;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pet;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\User;
use App\Services\DurationCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'DemoSeeder tidak boleh dijalankan di production.'
            );
        }

        DB::transaction(function () {
            $groomers = Groomer::query()
                ->orderBy('id')
                ->take(3)
                ->get();

            if ($groomers->count() < 3) {
                throw new RuntimeException(
                    'Tiga akun groomer harus tersedia. Jalankan GroomerSeeder terlebih dahulu.'
                );
            }

            $resepsionis = User::role('resepsionis')->first();

            if (! $resepsionis) {
                throw new RuntimeException(
                    'Akun resepsionis belum tersedia.'
                );
            }

            $calculator = app(DurationCalculator::class);

            // Customer demo
            $customer1 = Customer::firstOrCreate(
                ['no_wa' => '628123450001'],
                ['nama' => 'Rina Maharani']
            );

            $customer2 = Customer::firstOrCreate(
                ['no_wa' => '628123450002'],
                ['nama' => 'Budi Santoso']
            );

            $customer3 = Customer::firstOrCreate(
                ['no_wa' => '628123450003'],
                ['nama' => 'Dina Permata']
            );

            // Pet demo
            $pet1 = Pet::firstOrCreate(
                [
                    'customer_id' => $customer1->id,
                    'nama' => 'Kiko',
                ],
                [
                    'spesies' => 'anjing',
                    'ras' => 'Pomeranian',
                    'ukuran' => 'kecil',
                    'jenis_bulu' => 'panjang',
                    'perilaku' => 'normal',
                    'alergi' => null,
                    'catatan_kesehatan' => null,
                ]
            );

            $pet2 = Pet::firstOrCreate(
                [
                    'customer_id' => $customer2->id,
                    'nama' => 'Bruno',
                ],
                [
                    'spesies' => 'anjing',
                    'ras' => 'Golden Retriever',
                    'ukuran' => 'besar',
                    'jenis_bulu' => 'panjang',
                    'perilaku' => 'normal',
                    'alergi' => null,
                    'catatan_kesehatan' => null,
                ]
            );

            $pet3 = Pet::firstOrCreate(
                [
                    'customer_id' => $customer3->id,
                    'nama' => 'Milo',
                ],
                [
                    'spesies' => 'anjing',
                    'ras' => 'Beagle',
                    'ukuran' => 'sedang',
                    'jenis_bulu' => 'pendek',
                    'perilaku' => 'normal',
                    'alergi' => null,
                    'catatan_kesehatan' => null,
                ]
            );

            // Layanan demo
            $mandi = Service::firstOrCreate(
                ['nama' => 'Mandi dan Pengeringan'],
                [
                    'deskripsi' => 'Layanan mandi dan pengeringan bulu.',
                    'durasi_dasar' => 45,
                    'harga' => 50000,
                    'aktif' => true,
                ]
            );

            $potongBulu = Service::firstOrCreate(
                ['nama' => 'Potong Bulu'],
                [
                    'deskripsi' => 'Layanan pemotongan dan perapian bulu.',
                    'durasi_dasar' => 30,
                    'harga' => 40000,
                    'aktif' => true,
                ]
            );

            $potongKuku = Service::firstOrCreate(
                ['nama' => 'Potong Kuku'],
                [
                    'deskripsi' => 'Layanan pemotongan kuku.',
                    'durasi_dasar' => 15,
                    'harga' => 15000,
                    'aktif' => true,
                ]
            );

            /*
             * Membuat appointment beserta snapshot layanan.
             * Estimasi selalu dihitung melalui DurationCalculator.
             */
            $buatAppointment = function (
                string $key,
                Pet $pet,
                Groomer $groomer,
                Carbon $mulai,
                string $status,
                array $services,
                array $conditions = [],
                ?Carbon $waktuMulaiAktual = null,
                ?Carbon $waktuSelesaiAktual = null,
                ?string $alasanBatal = null
            ) use ($calculator): Appointment {
                $estimasi = $calculator->hitung(
                    $pet,
                    $services,
                    $conditions
                );

                $selesaiEstimasi = $mulai->copy()
                    ->addMinutes($estimasi->menit_total);

                $appointment = Appointment::updateOrCreate(
                    ['idempotency_key' => $key],
                    [
                        'pet_id' => $pet->id,
                        'groomer_id' => $groomer->id,
                        'mulai' => $mulai,
                        'selesai_estimasi' => $selesaiEstimasi,
                        'durasi_estimasi' => $estimasi->menit_total,
                        'status' => $status,
                        'token' => hash('sha256', $key),
                        'rincian_estimasi' => $estimasi->rincian,
                        'sumber' => 'demo',
                        'catatan_pelanggan' => 'Data demonstrasi PetCareOps.',
                        'alasan_batal' => $alasanBatal,
                        'waktu_mulai_aktual' => $waktuMulaiAktual,
                        'waktu_selesai_aktual' => $waktuSelesaiAktual,
                    ]
                );

                foreach ($services as $service) {
                    $appointment->services()->updateOrCreate(
                        ['service_id' => $service->id],
                        [
                            'nama_layanan' => $service->nama,
                            'durasi_dasar' => $service->durasi_dasar,
                            'harga' => $service->harga,
                        ]
                    );
                }

                return $appointment->refresh();
            };

            // Appointment 1: selesai, pembayaran sebagian
            $mulai1 = now()->subDays(21)->setTime(9, 0);
            $aktualMulai1 = $mulai1->copy()->addMinutes(5);
            $aktualSelesai1 = $aktualMulai1->copy()->addMinutes(55);

            $appointment1 = $buatAppointment(
                'demo-appointment-001',
                $pet1,
                $groomers[0],
                $mulai1,
                'selesai',
                [$mandi, $potongKuku],
                ['kusut'],
                $aktualMulai1,
                $aktualSelesai1
            );

            // Appointment 2: dijemput, pembayaran lunas
            $mulai2 = now()->subDays(35)->setTime(11, 0);
            $aktualMulai2 = $mulai2->copy()->addMinutes(5);
            $aktualSelesai2 = $aktualMulai2->copy()->addMinutes(75);

            $appointment2 = $buatAppointment(
                'demo-appointment-002',
                $pet2,
                $groomers[1],
                $mulai2,
                'dijemput',
                [$mandi, $potongBulu],
                ['kutu'],
                $aktualMulai2,
                $aktualSelesai2
            );

            // Appointment 3: menunggu
            $buatAppointment(
                'demo-appointment-003',
                $pet3,
                $groomers[2],
                now()->addDays(2)->setTime(13, 0),
                'menunggu',
                [$mandi, $potongKuku]
            );

            // Appointment 4: dibatalkan
            $buatAppointment(
                'demo-appointment-004',
                $pet1,
                $groomers[0],
                now()->subDays(5)->setTime(15, 0),
                'dibatalkan',
                [$mandi],
                [],
                null,
                null,
                'Pelanggan mengubah jadwal.'
            );

            // Grooming record untuk appointment selesai
            GroomingRecord::updateOrCreate(
                ['appointment_id' => $appointment1->id],
                [
                    'durasi_aktual' => 55,
                    'catatan_grooming' => 'Grooming selesai dengan baik.',
                ]
            );

            GroomingRecord::updateOrCreate(
                ['appointment_id' => $appointment2->id],
                [
                    'durasi_aktual' => 75,
                    'catatan_grooming' => 'Grooming selesai dan pet telah dijemput.',
                ]
            );

            // Invoice appointment 1: pembayaran sebagian
            $total1 = (int) $appointment1->services()->sum('harga');

            $invoice1 = Invoice::firstOrCreate(
                ['appointment_id' => $appointment1->id],
                [
                    'nomor_invoice' => 'INV-DEMO-001',
                    'total' => $total1,
                    'diterbitkan_pada' => $aktualSelesai1,
                ]
            );

            $nominalSebagian = max(1, intdiv($invoice1->total, 2));

            Payment::firstOrCreate(
                [
                    'invoice_id' => $invoice1->id,
                    'metode' => 'transfer',
                    'nominal' => $nominalSebagian,
                    'dicatat_oleh' => $resepsionis->id,
                ],
                [
                    'dibayar_pada' => $aktualSelesai1->copy()->addMinutes(10),
                ]
            );

            // Invoice appointment 2: pembayaran lunas
            $total2 = (int) $appointment2->services()->sum('harga');

            $invoice2 = Invoice::firstOrCreate(
                ['appointment_id' => $appointment2->id],
                [
                    'nomor_invoice' => 'INV-DEMO-002',
                    'total' => $total2,
                    'diterbitkan_pada' => $aktualSelesai2,
                ]
            );

            $nominalTunai = intdiv($invoice2->total, 2);
            $nominalQris = $invoice2->total - $nominalTunai;

            Payment::firstOrCreate(
                [
                    'invoice_id' => $invoice2->id,
                    'metode' => 'tunai',
                    'nominal' => $nominalTunai,
                    'dicatat_oleh' => $resepsionis->id,
                ],
                [
                    'dibayar_pada' => $aktualSelesai2->copy()->addMinutes(10),
                ]
            );

            Payment::firstOrCreate(
                [
                    'invoice_id' => $invoice2->id,
                    'metode' => 'qris',
                    'nominal' => $nominalQris,
                    'dicatat_oleh' => $resepsionis->id,
                ],
                [
                    'dibayar_pada' => $aktualSelesai2->copy()->addMinutes(15),
                ]
            );

            // Reminder appointment 1: masih menunggu
            Reminder::firstOrCreate(
                [
                    'pet_id' => $pet1->id,
                    'servis_terakhir_id' => $appointment1->id,
                ],
                [
                    'tanggal_jatuh_tempo' => $mulai1->copy()
                        ->addDays(28)->toDateString(),
                    'status' => 'menunggu',
                ]
            );

            // Reminder appointment 2: sudah terkirim
            Reminder::firstOrCreate(
                [
                    'pet_id' => $pet2->id,
                    'servis_terakhir_id' => $appointment2->id,
                ],
                [
                    'tanggal_jatuh_tempo' => $mulai2->copy()
                        ->addDays(28)->toDateString(),
                    'status' => 'terkirim',
                    'terkirim_at' => $mulai2->copy()
                        ->addDays(28)->setTime(8, 0),
                ]
            );
        });
    }
}
