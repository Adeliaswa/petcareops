<x-app-layout>
    <x-slot name="header"><h1 class="m5-page-title">Tugas Grooming</h1></x-slot>
    <section class="m5-page m5-groomer-page">
        @if (session('success'))<div class="m5-alert m5-alert--success">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="m5-alert m5-alert--error"><strong>Aksi belum berhasil.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="m5-groomer-welcome">
            <div><span class="m5-eyebrow">RUANG KERJA GROOMER</span><h2>Halo, {{ auth()->user()->name }}!</h2><p>Berikut tugas grooming yang menjadi tanggung jawabmu hari ini.</p><span class="m5-today">{{ now()->translatedFormat('l, d F Y') }}</span></div>
            <span class="m5-groomer-mark" aria-hidden="true">✂</span>
        </div>

        <div class="m5-task-summary">
            <div><span>Total tugas</span><strong>{{ $appointments->total() }}</strong></div>
            <div><span>Sedang dikerjakan</span><strong>{{ $appointments->getCollection()->where('status', 'dikerjakan')->count() }}</strong></div>
            <div><span>Selesai</span><strong>{{ $appointments->getCollection()->where('status', 'selesai')->count() }}</strong></div>
        </div>

        <div class="m5-section-heading"><div><h2>Daftar tugas hari ini</h2><p>Urut sesuai jadwal kedatangan.</p></div></div>
        @forelse($appointments->sortBy(fn($item) => $item->status === 'selesai' ? '1-'.$item->mulai->format('His') : '0-'.$item->mulai->format('His')) as $appointment)
            <article class="m5-task-card">
                <div class="m5-task-top">
                    <div class="m5-task-time"><strong>{{ $appointment->mulai->format('H:i') }}</strong><span>hingga {{ $appointment->selesai_estimasi->format('H:i') }}</span></div>
                    <span class="m5-status m5-status--{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                </div>
                <div class="m5-task-pet">
                    <span class="m5-pet-avatar">{{ strtoupper(mb_substr($appointment->pet?->nama ?? 'H', 0, 1)) }}</span>
                    <div><h3>{{ $appointment->pet?->nama ?? 'Data hewan tidak tersedia' }}</h3><p>{{ ucfirst($appointment->pet?->spesies ?? '-') }}{{ $appointment->pet?->ras ? ' · '.$appointment->pet->ras : '' }}</p></div>
                </div>
                <div class="m5-task-meta"><div><span>Pemilik</span><strong>{{ $appointment->pet?->customer?->nama ?? '-' }}</strong></div><div><span>Estimasi durasi</span><strong>{{ $appointment->durasi_estimasi }} menit</strong></div></div>
                <div class="m5-task-services"><span class="m5-task-label">Layanan</span><div class="m5-tags">@forelse($appointment->services as $service)<span>{{ $service->nama_layanan }}</span>@empty<span>-</span>@endforelse</div></div>

                <div class="m5-pet-notes">
                    <h4>Catatan khusus hewan</h4>
                    <dl>
                        <div><dt>Perilaku</dt><dd>{{ $appointment->pet?->perilaku ?: 'Tidak ada catatan' }}</dd></div>
                        <div><dt>Alergi</dt><dd>{{ $appointment->pet?->alergi ?: 'Tidak ada catatan' }}</dd></div>
                        <div><dt>Kesehatan</dt><dd>{{ $appointment->pet?->catatan_kesehatan ?: 'Tidak ada catatan' }}</dd></div>
                        <div><dt>Catatan pelanggan</dt><dd>{{ $appointment->catatan_pelanggan ?: 'Tidak ada catatan' }}</dd></div>
                    </dl>
                </div>

                @if ($appointment->status === 'menunggu' && $appointment->mulai->isToday())
                    <form method="POST" action="{{ route('groomer.tasks.start', $appointment) }}" onsubmit="this.querySelector('button').disabled=true">
                        @csrf
                        <button class="m5-button m5-button--primary m5-button--full m5-touch-button" type="submit">Mulai grooming</button>
                    </form>
                @elseif ($appointment->status === 'dikerjakan')
                    <form method="POST" action="{{ route('groomer.tasks.finish', $appointment) }}" class="m5-finish-form" onsubmit="this.querySelector('button').disabled=true">
                        @csrf
                        <label class="m5-field"><span>Catatan perawatan <em>(wajib)</em></span><textarea name="catatan_grooming" rows="4" maxlength="2000" required placeholder="Tuliskan kondisi hewan dan tindakan perawatan...">{{ old('catatan_grooming') }}</textarea></label>
                        <p class="m5-form-hint">Durasi aktual dihitung otomatis dari waktu mulai sampai tombol selesai ditekan.</p>
                        <button class="m5-button m5-button--success m5-button--full m5-touch-button" type="submit">Selesaikan grooming</button>
                    </form>
                @elseif ($appointment->status === 'selesai')
                    <div class="m5-task-complete">✓ Grooming selesai · {{ $appointment->groomingRecord?->durasi_aktual ?? '—' }} menit aktual</div>
                @endif
            </article>
        @empty
            <div class="m5-panel m5-empty"><span class="m5-empty-icon">♡</span><h3>Belum ada tugas grooming.</h3><p>Jadwal grooming yang ditugaskan kepadamu akan muncul di halaman ini.</p></div>
        @endforelse
        <div class="m5-pagination">{{ $appointments->links() }}</div>
    </section>
</x-app-layout>
