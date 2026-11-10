
<x-app-layout>
    <x-slot name="header">
        <h1 class="m5-page-title">Pengingat Grooming</h1>
    </x-slot>

    <section class="m6-page">

        {{-- HEADER --}}
        <div class="m6-heading">
            <div>
                <span class="m5-eyebrow">RETENSI PELANGGAN</span>
                <h2>Pengingat Grooming</h2>
                <p>
                    Pantau pelanggan yang sudah waktunya
                    melakukan perawatan kembali.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('staf.pengingat.jalankan') }}"
                  onsubmit="return confirm('Jalankan proses pengingat sekarang?')">
                @csrf

                <button class="m5-button m5-button--primary"
                        type="submit">
                    <span aria-hidden="true">↻</span>
                    Jalankan pengingat
                </button>
            </form>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="m5-alert m5-alert--success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="m5-alert m5-alert--error" role="alert">
                <strong>Proses belum berhasil.</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SUMMARY --}}
        @php
            $waiting = $reminders->getCollection()
                ->where('status', 'menunggu')->count();

            $sent = $reminders->getCollection()
                ->where('status', 'terkirim')->count();

            $closed = $reminders->getCollection()
                ->where('status', 'ditutup')->count();
        @endphp

        <div class="m6-stats">

            <article class="m6-stat">
                <span class="m6-stat-icon m6-icon-peach">◷</span>

                <div>
                    <small>Menunggu</small>
                    <strong>{{ $waiting }}</strong>
                    <span>di halaman ini</span>
                </div>
            </article>

            <article class="m6-stat">
                <span class="m6-stat-icon m6-icon-sage">✓</span>

                <div>
                    <small>Terkirim</small>
                    <strong>{{ $sent }}</strong>
                    <span>di halaman ini</span>
                </div>
            </article>

            <article class="m6-stat">
                <span class="m6-stat-icon m6-icon-neutral">—</span>

                <div>
                    <small>Ditutup</small>
                    <strong>{{ $closed }}</strong>
                    <span>di halaman ini</span>
                </div>
            </article>

        </div>

        {{-- DAFTAR PENGINGAT --}}
        <section class="m5-panel">

            <div class="m5-panel-heading">
                <div>
                    <h3>Daftar pengingat</h3>
                    <p>
                        Pengingat dibuat berdasarkan tanggal grooming
                        terakhir dan jarak pengingat salon.
                    </p>
                </div>

                <span class="m5-count">
                    {{ $reminders->total() }} data
                </span>
            </div>

            {{-- FILTER --}}
            <form method="GET"
                  action="{{ route('staf.pengingat.index') }}"
                  class="m6-filter">

                <label class="m5-field">
                    <span>Filter status</span>

                    <select name="status">
                        <option value="">Semua status</option>

                        <option value="menunggu"
                            @selected(request('status') === 'menunggu')>
                            Menunggu
                        </option>

                        <option value="terkirim"
                            @selected(request('status') === 'terkirim')>
                            Terkirim
                        </option>

                        <option value="ditutup"
                            @selected(request('status') === 'ditutup')>
                            Ditutup
                        </option>
                    </select>
                </label>

                <div class="m5-filter-actions">
                    <button class="m5-button m5-button--primary"
                            type="submit">
                        Terapkan
                    </button>

                    <a class="m5-button m5-button--light"
                       href="{{ route('staf.pengingat.index') }}">
                        Reset
                    </a>
                </div>

            </form>

            {{-- DATA --}}
            @if ($reminders->count())

                <div class="m6-reminder-list">

                    @foreach ($reminders as $reminder)

                        @php
                            $due = $reminder->tanggal_jatuh_tempo;

                            $late = $due
                                ? max(
                                    0,
                                    now()->startOfDay()->diffInDays(
                                        $due,
                                        false
                                    ) * -1
                                )
                                : 0;

                            $pet = $reminder->pet;
                            $owner = $pet?->customer;
                        @endphp

                        <article class="m6-reminder-row">

                            {{-- AVATAR --}}
                            <div class="m6-pet-avatar"
                                 aria-hidden="true">
                                {{ strtoupper(mb_substr(
                                    $pet?->nama ?? 'H',
                                    0,
                                    1
                                )) }}
                            </div>

                            {{-- INFORMASI --}}
                            <div class="m6-reminder-main">

                                <div class="m6-reminder-title">
                                    <strong>
                                        {{ $pet?->nama ?? 'Hewan tidak tersedia' }}
                                    </strong>

                                    <span class="m6-status m6-status--{{ $reminder->status }}">
                                        {{ ucfirst($reminder->status) }}
                                    </span>
                                </div>

                                <span class="m6-owner">
                                    {{ $owner?->nama ?? 'Pemilik tidak tersedia' }}

                                    {{ $owner?->no_wa
                                        ? ' · '.$owner->no_wa
                                        : '' }}
                                </span>

                                <div class="m6-reminder-meta">

                                    <span>
                                        <b>Jatuh tempo</b>
                                        {{ $due?->translatedFormat('d M Y') ?? '—' }}
                                    </span>

                                    <span class="{{ $late > 0 && $reminder->status === 'menunggu' ? 'm6-late' : '' }}">
                                        <b>Keterlambatan</b>
                                        {{ $late }} hari
                                    </span>

                                </div>

                            </div>

                            {{-- AKSI WHATSAPP --}}
                            <div class="m6-reminder-action">

                                @if (
                                    $reminder->status === 'menunggu'
                                    && $reminder->wa_link
                                )

                                    <a class="m5-button m5-button--success"
                                       href="{{ $reminder->wa_link }}"
                                       target="_blank"
                                       rel="noopener noreferrer">

                                        <span aria-hidden="true">↗</span>
                                        Buka WhatsApp

                                    </a>

                                @elseif ($reminder->status === 'terkirim')

                                    <small>
                                        Terkirim
                                        {{ $reminder->terkirim_at?->format('d M Y, H:i') ?? '' }}
                                    </small>

                                @else

                                    <small>Pengingat ditutup</small>

                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>

                {{-- PAGINATION --}}
                <div class="m5-pagination">

                    <span>
                        Menampilkan
                        {{ $reminders->firstItem() ?? 0 }}–{{ $reminders->lastItem() ?? 0 }}
                        dari {{ $reminders->total() }} data
                    </span>

                    {{ $reminders->links() }}

                </div>

            @else

                {{-- EMPTY STATE --}}
                <div class="m5-empty">

                    <span class="m5-empty-icon">♡</span>

                    <h3>Belum ada pengingat</h3>

                    <p>
                        Belum ada hewan yang masuk jadwal grooming
                        ulang untuk filter ini. Jalankan proses
                        pengingat untuk memperbarui daftar.
                    </p>

                    <form method="POST"
                          action="{{ route('staf.pengingat.jalankan') }}">
                        @csrf

                        <button class="m5-button m5-button--primary"
                                type="submit">
                            Jalankan pengingat
                        </button>
                    </form>

                </div>

            @endif

        </section>

        <p class="m6-footnote">
            Pengingat hanya dibuat untuk hewan yang memiliki
            riwayat grooming selesai dan belum memiliki
            appointment aktif di masa mendatang.
        </p>

    </section>
</x-app-layout>