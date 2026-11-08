<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\AppSetting;
use App\Models\Groomer;
use App\Models\Pet;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SlotFinderService
{
    private string $timezone = 'Asia/Jakarta';

    public function cari(
        string $tanggal,
        int $durasi,
        Pet $pet,
        array|Collection $daftarLayanan
    ): object {
        if ($durasi <= 0) {
            throw new BusinessRuleException(
                'Durasi grooming harus lebih dari 0 menit.'
            );
        }

        $layanan = collect($daftarLayanan);

        if ($layanan->isEmpty()) {
            throw new BusinessRuleException(
                'Pilih minimal satu layanan grooming.'
            );
        }

        $layanan = $layanan->map(function ($item) {
            if ($item instanceof Service) {
                return $item;
            }

            if (is_numeric($item)) {
                return Service::find($item);
            }

            return null;
        });

        if ($layanan->contains(fn ($item) => !($item instanceof Service))) {
            throw new BusinessRuleException(
                'Layanan yang dipilih tidak ditemukan.'
            );
        }

        foreach ($layanan as $item) {
            if (!$item->aktif) {
                throw new BusinessRuleException(
                    "Layanan {$item->nama} sudah tidak aktif."
                );
            }
        }

        try {
            $tanggalCari = CarbonImmutable::parse(
                $tanggal,
                $this->timezone
            )->startOfDay();
        } catch (\Throwable $e) {
            throw new BusinessRuleException(
                'Format tanggal pencarian tidak valid.'
            );
        }

        $sekarang = CarbonImmutable::now($this->timezone);

        $interval = (int) $this->setting('slot_interval', 30);
        $minimumLead = (int) $this->setting('batas_minimum_pesan', 60);
        $horizon = (int) $this->setting('horizon_hari', 14);

        if ($interval <= 0) {
            throw new BusinessRuleException(
                'Pengaturan interval slot tidak valid.'
            );
        }

        if ($tanggalCari->lt($sekarang->startOfDay())) {
            return $this->hasilKosong(
                'Tanggal yang dipilih sudah lewat.'
            );
        }

        if ($tanggalCari->gt($sekarang->addDays($horizon)->startOfDay())) {
            return $this->hasilKosong(
                'Tanggal berada di luar batas pemesanan.'
            );
        }

        // Standar ISO: Senin = 1 sampai Minggu = 7.
        $hari = $tanggalCari->dayOfWeekIso;

        $groomers = Groomer::query()
            ->with(['user', 'shifts', 'timeOffs'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        $groomersSesuai = $groomers
            ->filter(fn ($groomer) => $this->memenuhiKebutuhanPet(
                $groomer,
                $pet
            ))
            ->values();

        if ($groomersSesuai->isEmpty()) {
            return $this->hasilKosong(
                'Belum ada groomer yang sesuai dengan kebutuhan hewan.'
            );
        }

        $groomersDenganShift = $groomersSesuai
            ->filter(fn ($groomer) => $groomer->shifts->contains(
                'hari',
                $hari
            ))
            ->values();

        if ($groomersDenganShift->isEmpty()) {
            return $this->hasilKosong(
                'Tidak ada groomer yang memiliki shift pada tanggal tersebut.'
            );
        }

        // Groomer yang sedang cuti pada tanggal pencarian tidak tersedia.
        $groomersTersedia = $groomersDenganShift
            ->reject(function ($groomer) use ($tanggalCari) {
                $tanggal = $tanggalCari->toDateString();

                return $groomer->timeOffs->contains(
                    function ($timeOff) use ($tanggal) {
                        $mulaiCuti = $timeOff->tanggal_mulai->toDateString();
                        $selesaiCuti = $timeOff->tanggal_selesai->toDateString();

                        return $tanggal >= $mulaiCuti
                            && $tanggal <= $selesaiCuti;
                    }
                );
            })
            ->values();

        if ($groomersTersedia->isEmpty()) {
            return $this->hasilKosong(
                'Semua groomer yang sesuai sedang tidak tersedia atau cuti.'
            );
        }

        // Appointment selain yang dibatalkan tetap memblokir groomer.
        $appointments = DB::table('appointments')
            ->whereDate('mulai', $tanggalCari->toDateString())
            ->where('status', '!=', 'dibatalkan')
            ->get();

        // Beban kerja dihitung dari total durasi appointment aktif per groomer.
        $bebanKerja = $appointments
            ->groupBy('groomer_id')
            ->map(fn ($items) => (int) $items->sum('durasi_estimasi'));

        $slotPerWaktu = [];

        foreach ($groomersTersedia as $groomer) {
            $shiftHariIni = $groomer->shifts
                ->where('hari', $hari);

            foreach ($shiftHariIni as $shift) {
                $mulaiShift = CarbonImmutable::parse(
                    $tanggalCari->toDateString() . ' ' . $shift->jam_mulai,
                    $this->timezone
                );

                $selesaiShift = CarbonImmutable::parse(
                    $tanggalCari->toDateString() . ' ' . $shift->jam_selesai,
                    $this->timezone
                );

                // Mulai dari kelipatan interval berikutnya.
                $menitHari = ($mulaiShift->hour * 60)
                    + $mulaiShift->minute;

                $sisa = $menitHari % $interval;

                if ($sisa !== 0) {
                    $mulaiShift = $mulaiShift->addMinutes(
                        $interval - $sisa
                    );
                }

                $beban = (int) $bebanKerja->get($groomer->id, 0);

                while (true) {
                    $selesaiSlot = $mulaiShift->addMinutes($durasi);

                    // Slot harus selesai sebelum shift berakhir.
                    if ($selesaiSlot->gt($selesaiShift)) {
                        break;
                    }

                    // Tidak menawarkan slot yang sudah lewat
                    // atau terlalu dekat dengan waktu sekarang.
                    if ($mulaiShift->lt(
                        $sekarang->addMinutes($minimumLead)
                    )) {
                        $mulaiShift = $mulaiShift->addMinutes($interval);
                        continue;
                    }

                    $bentrok = $appointments->contains(
                        function ($appointment) use (
                            $groomer,
                            $mulaiShift,
                            $selesaiSlot
                        ) {
                            if (
                                (int) $appointment->groomer_id
                                !== (int) $groomer->id
                            ) {
                                return false;
                            }

                            $mulaiAppointment = CarbonImmutable::parse(
                                $appointment->mulai,
                                $this->timezone
                            );

                            $selesaiAppointment = CarbonImmutable::parse(
                                $appointment->selesai_estimasi,
                                $this->timezone
                            );

                            return $mulaiShift->lt($selesaiAppointment)
                                && $selesaiSlot->gt($mulaiAppointment);
                        }
                    );

                    if (!$bentrok) {
                        $key = $mulaiShift->format('Y-m-d H:i:s');

                        $slotPerWaktu[$key][] = [
                            'mulai' => $mulaiShift,
                            'selesai' => $selesaiSlot,
                            'groomer_id' => $groomer->id,
                            'beban_kerja_menit' => $beban,
                        ];
                    }

                    $mulaiShift = $mulaiShift->addMinutes($interval);
                }
            }
        }

        $slots = [];

        foreach ($slotPerWaktu as $kandidat) {
            // Beban terkecil menang; jika seri, ID groomer terkecil.
            usort($kandidat, function ($a, $b) {
                return ($a['beban_kerja_menit'] <=> $b['beban_kerja_menit'])
                    ?: ($a['groomer_id'] <=> $b['groomer_id']);
            });

            $pilihan = $kandidat[0];

            $slots[] = [
                'mulai' => $pilihan['mulai']->format('Y-m-d H:i:s'),
                'selesai' => $pilihan['selesai']->format('Y-m-d H:i:s'),
                'groomer_id' => $pilihan['groomer_id'],
            ];
        }

        usort(
            $slots,
            fn ($a, $b) => strcmp($a['mulai'], $b['mulai'])
        );

        if (empty($slots)) {
            return $this->hasilKosong(
                'Tidak ditemukan slot kosong yang sesuai.'
            );
        }

        return (object) [
            'slots' => $slots,
            'alasan' => null,
        ];
    }

    private function memenuhiKebutuhanPet(
        Groomer $groomer,
        Pet $pet
    ): bool {
        if ($pet->spesies === 'kucing' && !$groomer->bisa_kucing) {
            return false;
        }

        if ($pet->perilaku === 'agresif' && !$groomer->bisa_agresif) {
            return false;
        }

        $keySetting = 'skill_minimum_' . $pet->ukuran;
        $minimumSkill = $this->setting($keySetting, null);

        if (!$minimumSkill) {
            throw new BusinessRuleException(
                "Pengaturan minimum skill untuk ukuran {$pet->ukuran} belum tersedia."
            );
        }

        $tingkatSkill = [
            'dasar' => 1,
            'menengah' => 2,
            'ahli' => 3,
        ];

        $minimum = $tingkatSkill[$minimumSkill] ?? null;
        $dimiliki = $tingkatSkill[$groomer->level_skill] ?? null;

        if (!$minimum || !$dimiliki) {
            throw new BusinessRuleException(
                'Tingkat skill groomer tidak valid.'
            );
        }

        return $dimiliki >= $minimum;
    }

    private function setting(string $key, mixed $default): mixed
    {
        return AppSetting::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    private function hasilKosong(string $alasan): object
    {
        return (object) [
            'slots' => [],
            'alasan' => $alasan,
        ];
    }
}