<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\DurationFactor;
use App\Models\Pet;
use App\Models\Service;
use Illuminate\Support\Collection;

class DurationCalculator
{
    /**
     * Menghitung estimasi durasi grooming.
     *
     * @param Pet $pet
     * @param array|Collection $daftarLayanan
     * @param array $daftarKondisi Contoh: ['kusut', 'kutu']
     */
    public function hitung(
        Pet $pet,
        array|Collection $daftarLayanan,
        array $daftarKondisi = []
    ): object {
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

        if ($layanan->contains(fn ($item) => !$item instanceof Service)) {
            throw new BusinessRuleException(
                'Salah satu layanan yang dipilih tidak ditemukan.'
            );
        }

        foreach ($layanan as $item) {
            if (!$item->aktif) {
                throw new BusinessRuleException(
                    "Layanan {$item->nama} sudah tidak aktif."
                );
            }
        }

        $durasiDasar = $layanan->sum('durasi_dasar');

        $kategoriFaktor = [
            'ukuran' => $pet->ukuran,
            'jenis_bulu' => $pet->jenis_bulu,
            'perilaku' => $pet->perilaku,
        ];

        $keysKondisi = array_values(array_unique($daftarKondisi));

        $faktor = DurationFactor::query()
            ->where(function ($query) use ($kategoriFaktor, $keysKondisi, $pet) {
                foreach ($kategoriFaktor as $kategori => $key) {
                    $query->orWhere(function ($subQuery) use ($kategori, $key) {
                        $subQuery->where('kategori', $kategori)
                            ->where('key', $key);
                    });
                }

                $query->orWhere(function ($subQuery) use ($keysKondisi) {
                    $subQuery->where('kategori', 'kondisi')
                        ->whereIn('key', $keysKondisi);
                });

                $query->orWhere(function ($subQuery) use ($pet) {
                    $subQuery->where('kategori', 'buffer')
                        ->where('key', $pet->ukuran);
                });
            })
            ->get()
            ->keyBy(fn ($item) => $item->kategori . ':' . $item->key);

        $rincianFaktor = [];

        foreach ($kategoriFaktor as $kategori => $key) {
            $item = $faktor->get($kategori . ':' . $key);

            if (!$item) {
                throw new BusinessRuleException(
                    "Faktor durasi untuk {$kategori} '{$key}' belum tersedia."
                );
            }

            $rincianFaktor[$kategori] = [
                'label' => $item->label,
                'multiplier' => (float) $item->multiplier,
            ];
        }

        $durasiSetelahUkuran = $durasiDasar
            * $rincianFaktor['ukuran']['multiplier'];

        $durasiSetelahBulu = $durasiSetelahUkuran
            * $rincianFaktor['jenis_bulu']['multiplier'];

        $durasiSetelahPerilaku = $durasiSetelahBulu
            * $rincianFaktor['perilaku']['multiplier'];

        $tambahanFaktor = 0;
        $rincianTambahan = [];

        // Menit tambahan dari faktor perilaku, jika diatur di master data.
        $faktorPerilaku = $faktor->get(
            'perilaku:' . $pet->perilaku
        );

        if ($faktorPerilaku->extra_minutes > 0) {
            $tambahanFaktor += $faktorPerilaku->extra_minutes;

            $rincianTambahan[] = [
                'jenis' => 'perilaku',
                'label' => $faktorPerilaku->label,
                'menit' => $faktorPerilaku->extra_minutes,
            ];
        }

        foreach ($keysKondisi as $kondisi) {
            $item = $faktor->get('kondisi:' . $kondisi);

            if (!$item) {
                throw new BusinessRuleException(
                    "Faktor kondisi '{$kondisi}' belum tersedia."
                );
            }

            $tambahanFaktor += $item->extra_minutes;

            $rincianTambahan[] = [
                'jenis' => 'kondisi',
                'label' => $item->label,
                'menit' => $item->extra_minutes,
            ];
        }

        $buffer = $faktor->get('buffer:' . $pet->ukuran);

        if (!$buffer) {
            throw new BusinessRuleException(
                "Buffer pembersihan untuk ukuran '{$pet->ukuran}' belum tersedia."
            );
        }

        $menitBuffer = $buffer->extra_minutes;

        $durasiSebelumPembulatan = $durasiSetelahPerilaku
            + $tambahanFaktor
            + $menitBuffer;

        $durasiTotal = (int) (ceil($durasiSebelumPembulatan / 5) * 5);

        return (object) [
            'menit_total' => $durasiTotal,
            'rincian' => [
                'layanan' => $layanan->map(fn ($item) => [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'durasi_dasar' => $item->durasi_dasar,
                ])->values()->all(),

                'total_durasi_dasar' => $durasiDasar,
                'faktor' => $rincianFaktor,

                'durasi_setelah_ukuran' => $durasiSetelahUkuran,
                'durasi_setelah_bulu' => $durasiSetelahBulu,
                'durasi_setelah_perilaku' => $durasiSetelahPerilaku,

                'tambahan' => $rincianTambahan,
                'total_menit_tambahan' => $tambahanFaktor,

                'buffer_pembersihan' => $menitBuffer,
                'durasi_sebelum_pembulatan' => $durasiSebelumPembulatan,
                'menit_total' => $durasiTotal,
            ],
        ];
    }
}