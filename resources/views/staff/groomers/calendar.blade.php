<x-app-layout>
    <x-slot name="header"><h1 class="m5-page-title">Kalender Workload Groomer</h1></x-slot>
    <section class="m5-page">
        <div class="m5-page-heading">
            <div><span class="m5-eyebrow">PERENCANAAN OPERASIONAL</span><h2>Jadwal Groomer</h2><p>Pantau appointment berdasarkan tanggal dan penugasan groomer.</p></div>
        </div>
        <section class="m5-panel">
            <form method="GET" action="{{ route('staf.groomers.calendar') }}" class="m5-filter-grid m5-calendar-filter">
                <label class="m5-field"><span>Tanggal mulai</span><input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" required></label>
                <label class="m5-field"><span>Tanggal akhir</span><input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir }}" required></label>
                <label class="m5-field"><span>Groomer</span><select name="groomer_id"><option value="">Semua groomer</option>@foreach($groomers as $groomer)<option value="{{ $groomer->id }}" @selected((string)$groomerId === (string)$groomer->id)>{{ $groomer->user?->name ?? 'Groomer #'.$groomer->id }}</option>@endforeach</select></label>
                <div class="m5-filter-actions"><button class="m5-button m5-button--primary" type="submit">Tampilkan</button><a class="m5-button m5-button--light" href="{{ route('staf.groomers.calendar') }}">Reset</a></div>
            </form>
        </section>

        <div class="m5-calendar-legend">
            <span><i class="m5-legend-dot m5-legend-dot--waiting"></i>Menunggu</span>
            <span><i class="m5-legend-dot m5-legend-dot--working"></i>Dikerjakan</span>
            <span><i class="m5-legend-dot m5-legend-dot--done"></i>Selesai / dijemput</span>
            <span><i class="m5-legend-dot m5-legend-dot--cancelled"></i>Dibatalkan</span>
        </div>

        @php $cursor = \Carbon\Carbon::parse($tanggalMulai); $end = \Carbon\Carbon::parse($tanggalAkhir); @endphp
        @while($cursor->lte($end))
            @php
                $dateKey = $cursor->toDateString();
                $dayItems = $appointmentsByDate->get($dateKey, collect())->where('status', '!=', 'dibatalkan');
                $dayTotal = $dayItems->sum('durasi_estimasi');
            @endphp
            <section class="m5-panel m5-calendar-day">
                <div class="m5-panel-heading">
                    <div><h3>{{ $cursor->translatedFormat('l, d F Y') }}</h3><p>{{ $dayItems->count() }} appointment · {{ $dayTotal }} menit terjadwal</p></div>
                    <span class="m5-count">{{ $cursor->translatedFormat('D, d M') }}</span>
                </div>
                <div class="m5-groomer-board">
                    @forelse($groomers->when($groomerId, fn($items) => $items->where('id', $groomerId)) as $groomer)
                        @php
                            $groomerItems = $dayItems->where('groomer_id', $groomer->id)->sortBy('mulai');
                            $minutes = $groomerItems->sum('durasi_estimasi');
                        @endphp
                        <article class="m5-groomer-lane">
                            <header><div><strong>{{ $groomer->user?->name ?? 'Groomer #'.$groomer->id }}</strong><span>{{ $groomerItems->count() }} tugas</span></div><b>{{ $minutes }} <small>menit</small></b></header>
                            @forelse($groomerItems as $appointment)
                                <a class="m5-calendar-event m5-calendar-event--{{ $appointment->status }}" href="{{ route('staf.appointments.show', $appointment) }}">
                                    <span class="m5-event-time">{{ $appointment->mulai->format('H:i') }}–{{ $appointment->selesai_estimasi->format('H:i') }}</span>
                                    <strong>{{ $appointment->pet?->nama ?? 'Hewan' }}</strong>
                                    <span>{{ $appointment->services->pluck('nama_layanan')->filter()->join(', ') ?: 'Layanan grooming' }}</span>
                                    <small>{{ $appointment->durasi_estimasi }} menit · {{ ucfirst($appointment->status) }}</small>
                                </a>
                            @empty
                                <div class="m5-lane-empty">Belum ada appointment</div>
                            @endforelse
                        </article>
                    @empty
                        <div class="m5-empty m5-empty--compact"><h3>Belum ada groomer</h3><p>Data groomer aktif belum tersedia.</p></div>
                    @endforelse
                </div>
            </section>
            @php $cursor->addDay(); @endphp
        @endwhile
    </section>
</x-app-layout>
