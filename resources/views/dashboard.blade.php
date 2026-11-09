<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <section class="dashboard-welcome">
        <div class="dashboard-welcome__copy">
            <p class="eyebrow">PUSAT KENDALI OPERASIONAL</p>
            <h2>Halo, {{ auth()->user()->name }}! <span>♡</span></h2>
            <p>Berikut ringkasan aktivitas Pet Care Ops hari ini. Semoga operasional salon berjalan lancar.</p>
        </div>
        <div class="dashboard-welcome__art" aria-hidden="true">
            <img src="{{ asset('images/dog.svg') }}" alt="" class="dashboard-welcome__dog">
            <span class="dashboard-welcome__spark">✦</span>
        </div>
    </section>

    <section class="dashboard-stats" aria-label="Ringkasan operasional">
        <article class="stat-card stat-card--pink">
            <span class="stat-card__icon"><x-icon name="calendar" /></span>
            <p class="stat-card__label">Appointment hari ini</p>
            <strong class="stat-card__value">{{ number_format($stats['appointments_today']) }}</strong>
            <span class="stat-card__hint">Jadwal yang tidak dibatalkan</span>
        </article>
        <article class="stat-card stat-card--cream">
            <span class="stat-card__icon"><x-icon name="clock" /></span>
            <p class="stat-card__label">Menunggu</p>
            <strong class="stat-card__value">{{ number_format($stats['waiting_appointments']) }}</strong>
            <span class="stat-card__hint">Appointment berstatus menunggu</span>
        </article>
        <article class="stat-card stat-card--white">
            <span class="stat-card__icon"><x-icon name="users" /></span>
            <p class="stat-card__label">Pemilik terdaftar</p>
            <strong class="stat-card__value">{{ number_format($stats['customers']) }}</strong>
            <span class="stat-card__hint">Data pemilik hewan</span>
        </article>
        <article class="stat-card stat-card--white">
            <span class="stat-card__icon"><x-icon name="paw" /></span>
            <p class="stat-card__label">Hewan terdaftar</p>
            <strong class="stat-card__value">{{ number_format($stats['pets']) }}</strong>
            <span class="stat-card__hint">Data hewan pelanggan</span>
        </article>
        <article class="stat-card stat-card--sage">
            <span class="stat-card__icon"><x-icon name="scissors" /></span>
            <p class="stat-card__label">Groomer aktif</p>
            <strong class="stat-card__value">{{ number_format($stats['active_groomers']) }}</strong>
            <span class="stat-card__hint">Akun groomer aktif</span>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="card dashboard-appointments">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">JADWAL SALON</p>
                    <h2 class="section-heading__title">Appointment hari ini</h2>
                    <p class="section-heading__desc">Jadwal yang tercatat untuk {{ now()->translatedFormat('d F Y') }}.</p>
                </div>
                <a href="{{ route('staf.appointments.index') }}" class="text-link">Lihat semua <x-icon name="arrow-up-right" /></a>
            </div>

            @if ($todayAppointments->isEmpty())
                <div class="dashboard-empty">
                    <img src="{{ asset('images/cat.svg') }}" alt="" aria-hidden="true">
                    <div><strong>Belum ada appointment hari ini</strong><p>Jadwal yang masuk akan muncul di bagian ini.</p></div>
                </div>
            @else
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead><tr><th>Waktu</th><th>Hewan & pemilik</th><th>Groomer</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($todayAppointments as $appointment)
                            <tr>
                                <td class="tabular"><strong>{{ $appointment->mulai->format('H:i') }}</strong><small>{{ $appointment->durasi_estimasi }} menit</small></td>
                                <td><strong>{{ $appointment->pet?->nama ?? 'Hewan' }}</strong><small>{{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}</small></td>
                                <td>{{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}</td>
                                <td><span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($appointment->status) }}">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>

        <aside class="dashboard-shortcuts card">
            <p class="eyebrow">AKSES CEPAT</p>
            <h2 class="section-heading__title">Kelola salon</h2>
            <p class="section-heading__desc">Buka bagian yang sering digunakan untuk mengelola operasional.</p>
            <a href="{{ route('admin.staff.index') }}" class="shortcut-link"><span class="shortcut-link__icon"><x-icon name="user-cog" /></span><span><strong>Akun staf</strong><small>Kelola akun dan akses staf</small></span><x-icon name="arrow-up-right" /></a>
            <a href="{{ route('admin.pages.services') }}" class="shortcut-link"><span class="shortcut-link__icon"><x-icon name="tag" /></span><span><strong>Layanan grooming</strong><small>Atur daftar layanan salon</small></span><x-icon name="arrow-up-right" /></a>
            <a href="{{ route('admin.pages.groomers') }}" class="shortcut-link"><span class="shortcut-link__icon"><x-icon name="scissors" /></span><span><strong>Data groomer</strong><small>Lihat profil tim groomer</small></span><x-icon name="arrow-up-right" /></a>
            <a href="{{ route('profile.edit') }}" class="shortcut-link"><span class="shortcut-link__icon"><x-icon name="user" /></span><span><strong>Profil saya</strong><small>Perbarui informasi akun</small></span><x-icon name="arrow-up-right" /></a>
        </aside>
    </section>
</x-app-layout>
