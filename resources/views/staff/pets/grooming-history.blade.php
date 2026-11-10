<x-app-layout>
    <x-slot name="header"><h1 class="m5-page-title">Riwayat Grooming</h1></x-slot>
    <section class="m5-page">
        <a class="m5-back-link" href="{{ route('staf.pages.pets') }}">← Kembali ke daftar hewan</a>
        <section class="m5-pet-profile">
            <span class="m5-pet-avatar m5-pet-avatar--large">{{ strtoupper(mb_substr($pet->nama, 0, 1)) }}</span>
            <div><span class="m5-eyebrow">PROFIL HEWAN</span><h2>{{ $pet->nama }}</h2><p>{{ ucfirst($pet->spesies) }}{{ $pet->ras ? ' · '.$pet->ras : '' }} · Pemilik: {{ $pet->customer?->nama ?? '-' }}</p></div>
        </section>
        <section class="m5-panel">
            <div class="m5-panel-heading"><div><h3>Riwayat perawatan</h3><p>Catatan kunjungan terbaru ditampilkan terlebih dahulu.</p></div><span class="m5-count">{{ $groomingRecords->total() }} kunjungan</span></div>
            @forelse($groomingRecords as $appointment)
                @php
                    $record = $appointment->groomingRecord;
                    $estimated = (int) $appointment->durasi_estimasi;
                    $actual = (int) ($record?->durasi_aktual ?? 0);
                    $difference = $actual - $estimated;
                @endphp
                <article class="m5-history-item">
                    <div class="m5-history-date"><span>{{ $appointment->mulai->translatedFormat('d') }}</span><small>{{ $appointment->mulai->translatedFormat('M Y') }}</small></div>
                    <div class="m5-history-content">
                        <div class="m5-history-heading"><div><h3>{{ $appointment->services->pluck('nama_layanan')->filter()->join(', ') ?: 'Grooming' }}</h3><p>{{ $appointment->mulai->format('H:i') }} · Groomer {{ $appointment->groomer?->user?->name ?? '-' }}</p></div><span class="m5-status m5-status--{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></div>
                        <div class="m5-history-metrics"><span>Estimasi <strong>{{ $estimated }} menit</strong></span><span>Aktual <strong>{{ $record?->durasi_aktual ?? '—' }}{{ $record ? ' menit' : '' }}</strong></span><span>Selisih <strong>{{ $record ? (($difference > 0 ? '+' : '').$difference.' menit') : '—' }}</strong></span></div>
                        @if($record?->catatan_grooming)<div class="m5-note"><strong>Catatan perawatan</strong><p>{{ $record->catatan_grooming }}</p></div>@endif
                    </div>
                </article>
            @empty
                <div class="m5-empty"><span class="m5-empty-icon">♡</span><h3>Belum ada riwayat grooming</h3><p>Riwayat akan tercatat setelah grooming selesai dan catatan perawatan disimpan.</p></div>
            @endforelse
            <div class="m5-pagination">{{ $groomingRecords->links() }}</div>
        </section>
    </section>
</x-app-layout>
