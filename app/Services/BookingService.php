<?php

namespace App\Services;

use App\Events\BookingDibuat;
use App\Exceptions\BookingException;
use App\Exceptions\BusinessRuleException;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class BookingService
{
    private string $timezone = 'Asia/Jakarta';

    public function __construct(
        private DurationCalculator $durationCalculator,
        private SlotFinderService $slotFinder,
        private PhoneNormalizer $phoneNormalizer
    ) {}

    /**
     * Membuat booking publik atau walk-in.
     *
     * sumber: publik | walk-in
     */
    public function buat(
        array $data,
        string $sumber = 'publik'
    ): Appointment {
        if (! in_array($sumber, ['publik', 'walk-in'], true)) {
            throw new BookingException('Sumber booking tidak valid.');
        }

        try {
            $data['no_wa'] = $this->phoneNormalizer->normalize(
                $data['no_wa'] ?? ''
            );
        } catch (InvalidArgumentException) {
            throw new BookingException(
                'Format nomor WhatsApp tidak valid.'
            );
        }

        $validator = Validator::make($data, [
            'no_wa' => ['required', 'string', 'max:20'],
            'nama_pelanggan' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'integer'],
            'pet_id' => ['nullable', 'integer'],
            'pet' => ['required_without:pet_id', 'array'],
            'pet.nama' => ['required_without:pet_id', 'string', 'max:255'],
            'pet.spesies' => ['required_without:pet_id', 'in:anjing,kucing'],
            'pet.ras' => ['nullable', 'string', 'max:255'],
            'pet.ukuran' => ['required_without:pet_id', 'string', 'max:30'],
            'pet.jenis_bulu' => ['required_without:pet_id', 'string', 'max:50'],
            'pet.perilaku' => ['required_without:pet_id', 'string', 'max:30'],
            'pet.alergi' => ['nullable', 'string'],
            'pet.catatan_kesehatan' => ['nullable', 'string'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'distinct'],
            'kondisi' => ['sometimes', 'array'],
            'kondisi.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists('duration_factors', 'key')
                    ->where(fn ($query) => $query->where('kategori', 'kondisi')),
            ],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'mulai' => ['required', 'date_format:Y-m-d H:i:s'],
            'catatan_pelanggan' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['required', 'string', 'min:20', 'max:64'],
        ]);

        if ($validator->fails()) {
            throw new BookingException(
                $validator->errors()->first()
            );
        }

        $data = $validator->validated();

        try {
            return DB::transaction(function () use ($data, $sumber) {
                // Mencegah pengiriman ulang dengan kunci yang sama.
                if (
                    Appointment::where(
                        'idempotency_key',
                        $data['idempotency_key']
                    )->exists()
                ) {
                    throw new BookingException(
                        'Permintaan booking ini sudah pernah diproses.'
                    );
                }

                // Cari pelanggan berdasarkan nomor WhatsApp.
                $customer = Customer::query()
                    ->where('no_wa', $data['no_wa'])
                    ->lockForUpdate()
                    ->first();

                if ($data['customer_id'] ?? null) {
                    if (
                        ! $customer ||
                        (int) $customer->id !== (int) $data['customer_id']
                    ) {
                        throw new BookingException(
                            'Data pelanggan tidak sesuai dengan nomor WhatsApp.'
                        );
                    }
                }

                // Pelanggan baru hanya dibuat di dalam transaksi.
                if (! $customer) {
                    if (empty($data['nama_pelanggan'])) {
                        throw new BookingException(
                            'Nama pelanggan wajib diisi.'
                        );
                    }

                    $customer = Customer::create([
                        'nama' => $data['nama_pelanggan'],
                        'no_wa' => $data['no_wa'],
                    ]);
                }

                // Ambil pet lama atau buat pet baru.
                if (! empty($data['pet_id'])) {
                    $pet = Pet::query()
                        ->where('customer_id', $customer->id)
                        ->whereKey($data['pet_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $pet) {
                        throw new BookingException(
                            'Data hewan tidak ditemukan pada pelanggan tersebut.'
                        );
                    }
                } else {
                    $pet = Pet::create(array_merge(
                        $data['pet'],
                        ['customer_id' => $customer->id]
                    ));
                }

                // Kunci layanan agar harga dan durasi tidak berubah
                // selama proses penyimpanan booking.
                $serviceIds = collect($data['service_ids'])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $services = Service::query()
                    ->whereIn('id', $serviceIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($services->count() !== $serviceIds->count()) {
                    throw new BookingException(
                        'Salah satu layanan tidak ditemukan atau sudah dihapus.'
                    );
                }

                foreach ($services as $service) {
                    if (! $service->aktif) {
                        throw new BookingException(
                            "Layanan {$service->nama} sudah tidak aktif."
                        );
                    }
                }

                // Hitung durasi menggunakan kalkulator M3.
                try {
                    $estimasi = $this->durationCalculator->hitung(
                        $pet,
                        $services,
                        $data['kondisi'] ?? []
                    );
                } catch (BusinessRuleException $e) {
                    throw new BookingException($e->getMessage());
                }

                $durasi = (int) $estimasi->menit_total;

                $mulai = CarbonImmutable::parse(
                    $data['mulai'],
                    $this->timezone
                );

                if ($mulai->toDateString() !== $data['tanggal']) {
                    throw new BookingException(
                        'Tanggal dan waktu booking tidak sesuai.'
                    );
                }

                $selesai = $mulai->addMinutes($durasi);

                // Booking publik memakai slot dari M3.
                // Walk-in memakai pemeriksaan khusus tanpa batas
                // minimum lead time 60 menit.
                if ($sumber === 'publik') {
                    $groomerId = $this->cariGroomerPublik(
                        $data['tanggal'],
                        $durasi,
                        $pet,
                        $services,
                        $mulai
                    );
                } else {
                    $groomerId = $this->cariGroomerWalkIn(
                        $pet,
                        $mulai,
                        $selesai
                    );
                }

                // Kunci groomer terpilih dan periksa ulang.
                $groomer = Groomer::query()
                    ->whereKey($groomerId)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $groomer ||
                    ! $this->groomerMemenuhiKebutuhanPet($groomer, $pet)
                ) {
                    throw new BookingException(
                        'Groomer tidak lagi tersedia. Silakan pilih slot lain.'
                    );
                }

                $groomer->load(['user', 'shifts', 'timeOffs']);

                if (
                    ! $groomer->user ||
                    ! $groomer->user->is_active ||
                    ! $this->groomerMemilikiJadwal(
                        $groomer,
                        $mulai,
                        $selesai
                    )
                ) {
                    throw new BookingException(
                        'Groomer tidak lagi tersedia. Silakan pilih slot lain.'
                    );
                }

                // Penguncian dan pengecekan ulang bentrok groomer.
                $bentrokGroomer = Appointment::query()
                    ->where('groomer_id', $groomer->id)
                    ->where('status', '!=', 'dibatalkan')
                    ->where('mulai', '<', $selesai)
                    ->where('selesai_estimasi', '>', $mulai)
                    ->lockForUpdate()
                    ->exists();

                if ($bentrokGroomer) {
                    throw new BookingException(
                        'Slot baru saja terisi, silakan pilih slot lain.'
                    );
                }

                // Satu pet tidak boleh memiliki dua booking aktif
                // yang waktunya saling bertumpukan.
                $bentrokPet = Appointment::query()
                    ->where('pet_id', $pet->id)
                    ->where('status', '!=', 'dibatalkan')
                    ->where('mulai', '<', $selesai)
                    ->where('selesai_estimasi', '>', $mulai)
                    ->lockForUpdate()
                    ->exists();

                if ($bentrokPet) {
                    throw new BookingException(
                        'Hewan sudah memiliki jadwal pada waktu tersebut.'
                    );
                }

                // Simpan appointment.
                $appointment = Appointment::create([
                    'pet_id' => $pet->id,
                    'groomer_id' => $groomer->id,
                    'mulai' => $mulai,
                    'selesai_estimasi' => $selesai,
                    'durasi_estimasi' => $durasi,
                    'status' => 'menunggu',
                    'token' => Str::random(64),
                    'rincian_estimasi' => $estimasi->rincian,
                    'sumber' => $sumber,
                    'catatan_pelanggan' => $data['catatan_pelanggan'] ?? null,
                    'idempotency_key' => $data['idempotency_key'],
                ]);

                // Simpan snapshot nama, durasi dasar, dan harga
                // setiap layanan agar riwayat booking tidak berubah.
                foreach ($services as $service) {
                    AppointmentService::create([
                        'appointment_id' => $appointment->id,
                        'service_id' => $service->id,
                        'nama_layanan' => $service->nama,
                        'durasi_dasar' => $service->durasi_dasar,
                        'harga' => $service->harga,
                    ]);
                }

                // Event hanya dijalankan setelah transaksi berhasil.
                DB::afterCommit(function () use ($appointment) {
                    BookingDibuat::dispatch($appointment);
                });

                return $appointment->load([
                    'pet',
                    'groomer.user',
                    'services',
                ]);
            }, 3);
        } catch (BookingException $e) {
            throw $e;
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? (string) $e->getCode();

            // PostgreSQL exclusion constraint: bentrok jadwal.
            if ($sqlState === '23P01') {
                throw new BookingException(
                    'Slot baru saja terisi, silakan pilih slot lain.'
                );
            }

            // Unique constraint idempotency key.
            if (
                $sqlState === '23505' &&
                str_contains($e->getMessage(), 'idempotency')
            ) {
                throw new BookingException(
                    'Permintaan booking ini sudah pernah diproses.'
                );
            }

            throw $e;
        }
    }

    /**
     * Mencari groomer dari hasil SlotFinderService M3.
     */
    private function cariGroomerPublik(
        string $tanggal,
        int $durasi,
        Pet $pet,
        $services,
        CarbonImmutable $mulai
    ): int {
        try {
            $hasil = $this->slotFinder->cari(
                $tanggal,
                $durasi,
                $pet,
                $services
            );
        } catch (BusinessRuleException $e) {
            throw new BookingException($e->getMessage());
        }

        $slot = collect($hasil->slots)->first(
            fn ($item) => $item['mulai'] === $mulai->format('Y-m-d H:i:s')
        );

        if (! $slot) {
            throw new BookingException(
                'Slot baru saja terisi, silakan pilih slot lain.'
            );
        }

        return (int) $slot['groomer_id'];
    }

    /**
     * Pencarian groomer walk-in.
     * Aturan skill, shift, cuti, dan bentrok tetap berlaku.
     * Hanya batas minimum lead time yang dikecualikan.
     */
    private function cariGroomerWalkIn(
        Pet $pet,
        CarbonImmutable $mulai,
        CarbonImmutable $selesai
    ): int {
        $sekarang = CarbonImmutable::now($this->timezone);

        if ($mulai->lt($sekarang)) {
            throw new BookingException(
                'Waktu walk-in tidak boleh berada di masa lalu.'
            );
        }

        $interval = (int) $this->setting('slot_interval', 30);

        if ($interval <= 0) {
            throw new BookingException(
                'Pengaturan interval slot tidak valid.'
            );
        }

        $menitHari = ($mulai->hour * 60) + $mulai->minute;

        if ($menitHari % $interval !== 0 || $mulai->second !== 0) {
            throw new BookingException(
                'Waktu walk-in harus mengikuti interval slot.'
            );
        }

        $groomers = Groomer::query()
            ->with(['user', 'shifts', 'timeOffs'])
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->get();

        $kandidat = $groomers->filter(function ($groomer) use (
            $pet,
            $mulai,
            $selesai
        ) {
            if (! $this->groomerMemenuhiKebutuhanPet($groomer, $pet)) {
                return false;
            }

            if (! $this->groomerMemilikiJadwal(
                $groomer,
                $mulai,
                $selesai
            )) {
                return false;
            }

            $tanggal = $mulai->toDateString();

            $sedangCuti = $groomer->timeOffs->contains(
                fn ($cuti) => $tanggal >= $cuti->tanggal_mulai->toDateString()
                    && $tanggal <= $cuti->tanggal_selesai->toDateString()
            );

            if ($sedangCuti) {
                return false;
            }

            $bentrok = Appointment::query()
                ->where('groomer_id', $groomer->id)
                ->where('status', '!=', 'dibatalkan')
                ->where('mulai', '<', $selesai)
                ->where('selesai_estimasi', '>', $mulai)
                ->exists();

            return ! $bentrok;
        });

        if ($kandidat->isEmpty()) {
            throw new BookingException(
                'Tidak ada groomer yang tersedia untuk waktu walk-in tersebut.'
            );
        }

        // Terapkan aturan least-load M3.
        $beban = Appointment::query()
            ->whereDate('mulai', $mulai->toDateString())
            ->where('status', '!=', 'dibatalkan')
            ->get()
            ->groupBy('groomer_id')
            ->map(fn ($items) => (int) $items->sum('durasi_estimasi'));

        return (int) $kandidat
            ->sort(function ($a, $b) use ($beban) {
                return (
                    $beban->get($a->id, 0)
                    <=> $beban->get($b->id, 0)
                ) ?: ($a->id <=> $b->id);
            })
            ->first()
            ->id;
    }

    /**
     * Memeriksa skill groomer terhadap kebutuhan pet.
     */
    private function groomerMemenuhiKebutuhanPet(
        Groomer $groomer,
        Pet $pet
    ): bool {
        if ($pet->spesies === 'kucing' && ! $groomer->bisa_kucing) {
            return false;
        }

        if ($pet->perilaku === 'agresif' && ! $groomer->bisa_agresif) {
            return false;
        }

        $minimumSkill = $this->setting(
            'skill_minimum_'.$pet->ukuran,
            null
        );

        $tingkatSkill = [
            'dasar' => 1,
            'menengah' => 2,
            'ahli' => 3,
        ];

        $minimum = $tingkatSkill[$minimumSkill] ?? null;
        $dimiliki = $tingkatSkill[$groomer->level_skill] ?? null;

        if (! $minimum || ! $dimiliki) {
            throw new BookingException(
                'Pengaturan skill groomer tidak valid.'
            );
        }

        return $dimiliki >= $minimum;
    }

    /**
     * Memastikan groomer memiliki shift yang mencakup seluruh durasi.
     */
    private function groomerMemilikiJadwal(
        Groomer $groomer,
        CarbonImmutable $mulai,
        CarbonImmutable $selesai
    ): bool {
        $hari = $mulai->dayOfWeekIso;

        return $groomer->shifts
            ->where('hari', $hari)
            ->contains(function ($shift) use ($mulai, $selesai) {
                $awalShift = CarbonImmutable::parse(
                    $mulai->toDateString().' '.$shift->jam_mulai,
                    $this->timezone
                );

                $akhirShift = CarbonImmutable::parse(
                    $mulai->toDateString().' '.$shift->jam_selesai,
                    $this->timezone
                );

                return $mulai->gte($awalShift)
                    && $selesai->lte($akhirShift);
            });
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        return AppSetting::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}