
<x-app-layout>
    <x-slot name="header">
        <h1 class="m5-page-title">Appointment</h1>
    </x-slot>

    <section class="m5-page">

        {{-- PAGE HEADING --}}
        <div class="m5-page-heading">
            <div>
                <span class="m5-eyebrow">OPERASIONAL HARIAN</span>

                <h2>Daftar Appointment</h2>

                <p>
                    Pantau jadwal grooming, pemilik, layanan,
                    status antrean, dan pembayaran.
                </p>
            </div>

            <a class="m5-button m5-button--primary"
               href="{{ route('staf.booking.create') }}">

                <span aria-hidden="true">＋</span>
                Booking walk-in

            </a>
        </div>

        {{-- STATISTICS --}}
        <div class="m5-stat-grid">

            <article class="m5-stat-card">

                <span class="m5-stat-icon m5-stat-icon--brown">
                    ▦
                </span>

                <div>
                    <span class="m5-stat-label">
                        Hasil pencarian
                    </span>

                    <strong>
                        {{ $appointments->total() }}
                    </strong>

                    <small>appointment ditemukan</small>
                </div>

            </article>

            <article class="m5-stat-card">

                <span class="m5-stat-icon m5-stat-icon--peach">
                    ◷
                </span>

                <div>
                    <span class="m5-stat-label">
                        Menunggu
                    </span>

                    <strong>
                        {{ $appointments->getCollection()->where('status', 'menunggu')->count() }}
                    </strong>

                    <small>di halaman ini</small>
                </div>

            </article>

            <article class="m5-stat-card">

                <span class="m5-stat-icon m5-stat-icon--sage">
                    ✓
                </span>

                <div>
                    <span class="m5-stat-label">
                        Selesai
                    </span>

                    <strong>
                        {{ $appointments->getCollection()->whereIn('status', ['selesai', 'dijemput'])->count() }}
                    </strong>

                    <small>di halaman ini</small>
                </div>

            </article>

        </div>

        {{-- FILTER DAN PENCARIAN --}}
        <section class="m5-panel">

            <div class="m5-panel-heading">

                <div>
                    <h3>Filter dan pencarian</h3>

                    <p>
                        Temukan appointment berdasarkan nama,
                        status, groomer, atau tanggal.
                    </p>
                </div>

                <span class="m5-count">
                    {{ $appointments->total() }} data
                </span>

            </div>

            <form method="GET"
                  action="{{ route('staf.appointments.index') }}"
                  class="m5-filter-grid">

                {{-- PENCARIAN --}}
                <label class="m5-field m5-field--search">

                    <span>Cari hewan atau pemilik</span>

                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Contoh: Lancelot atau Rhea"
                    >

                </label>

                {{-- FILTER STATUS --}}
                <label class="m5-field">

                    <span>Status</span>

                    <select name="status">

                        <option value="">Semua status</option>

                        @foreach ([
                            'menunggu' => 'Menunggu',
                            'dikerjakan' => 'Dikerjakan',
                            'selesai' => 'Selesai',
                            'dijemput' => 'Dijemput',
                            'dibatalkan' => 'Dibatalkan',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(($filters['status'] ?? '') === $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </label>

                {{-- FILTER GROOMER --}}
                <label class="m5-field">

                    <span>Groomer</span>

                    <select name="groomer_id">

                        <option value="">Semua groomer</option>

                        @foreach ($groomers as $groomer)

                            <option
                                value="{{ $groomer->id }}"
                                @selected(
                                    (string) ($filters['groomer_id'] ?? '')
                                    === (string) $groomer->id
                                )
                            >
                                {{ $groomer->user?->name ?? 'Groomer #' . $groomer->id }}
                            </option>

                        @endforeach

                    </select>

                </label>

                {{-- FILTER TANGGAL --}}
                <label class="m5-field">

                    <span>Tanggal</span>

                    <input
                        type="date"
                        name="tanggal"
                        value="{{ $filters['tanggal'] ?? '' }}"
                    >

                </label>

                {{-- ACTION --}}
                <div class="m5-filter-actions">

                    <button
                        class="m5-button m5-button--primary"
                        type="submit"
                    >
                        Terapkan
                    </button>

                    <a
                        class="m5-button m5-button--light"
                        href="{{ route('staf.appointments.index') }}"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </section>

        {{-- TABEL APPOINTMENT --}}
        <section class="m5-panel">

            <div class="m5-panel-heading">

                <div>
                    <h3>Jadwal appointment</h3>

                    <p>
                        Urutan berdasarkan waktu mulai terbaru.
                    </p>
                </div>

            </div>

            @if ($appointments->count() > 0)

                <div class="m5-table-wrap">

                    <table class="m5-table">

                        <thead>

                            <tr>
                                <th>Waktu</th>
                                <th>Hewan &amp; Pemilik</th>
                                <th>Layanan</th>
                                <th>Groomer</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($appointments as $appointment)

                                <tr>

                                    {{-- WAKTU --}}
                                    <td>

                                        <strong class="m5-time">
                                            {{ $appointment->mulai?->format('H:i') ?? '-' }}
                                        </strong>

                                        <span class="m5-subtext">
                                            {{ $appointment->mulai?->translatedFormat('d M Y') ?? '-' }}
                                        </span>

                                    </td>

                                    {{-- HEWAN DAN PEMILIK --}}
                                    <td>

                                        <div class="m5-person">

                                            <strong>
                                                {{ $appointment->pet?->nama ?? 'Hewan dihapus' }}
                                            </strong>

                                            <span>
                                                {{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}
                                            </span>

                                        </div>

                                    </td>

                                    {{-- LAYANAN --}}
                                    <td>

                                        <div class="m5-tags">

                                            @forelse ($appointment->services as $service)

                                                <span>
                                                    {{ $service->nama_layanan }}
                                                </span>

                                            @empty

                                                <span class="m5-subtext">
                                                    Belum ada layanan
                                                </span>

                                            @endforelse

                                        </div>

                                    </td>

                                    {{-- GROOMER --}}
                                    <td>
                                        {{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}
                                    </td>

                                    {{-- STATUS APPOINTMENT --}}
                                    <td>

                                        <span class="m5-status m5-status--{{ $appointment->status }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>

                                    </td>

                                    {{-- STATUS PEMBAYARAN M6 --}}
                                    <td>

                                        @if (in_array($appointment->status, ['selesai', 'dijemput']))

                                            @if ($appointment->invoice)

                                                @php
                                                    $payStatus = $appointment->invoice->status_pembayaran;
                                                @endphp

                                                <span class="m6-status m6-status--{{ $payStatus }}">

                                                    {{ [
                                                        'belum_bayar' => 'Belum bayar',
                                                        'sebagian' => 'Sebagian',
                                                        'lunas' => 'Lunas',
                                                    ][$payStatus] ?? ucfirst($payStatus) }}

                                                </span>

                                            @else

                                                <span class="m6-status m6-status--belum_bayar">
                                                    Belum ditagihkan
                                                </span>

                                            @endif

                                        @else

                                            <span class="m5-subtext">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                    {{-- DETAIL --}}
                                    <td>

                                        <a
                                            class="m5-text-link"
                                            href="{{ route('staf.appointments.show', $appointment) }}"
                                        >
                                            Detail
                                            <span aria-hidden="true">→</span>
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- PAGINATION --}}
                <div class="m5-pagination">

                    <span>
                        Menampilkan
                        {{ $appointments->firstItem() ?? 0 }}–{{ $appointments->lastItem() ?? 0 }}
                        dari {{ $appointments->total() }} data
                    </span>

                    {{ $appointments->links() }}

                </div>

            @else

                {{-- EMPTY STATE --}}
                <div class="m5-empty">

                    <span class="m5-empty-icon">▦</span>

                    <h3>Belum ada appointment</h3>

                    <p>
                        Tidak ada jadwal yang cocok dengan filter ini.
                        Coba ubah pencarian atau buat booking walk-in.
                    </p>

                    <a
                        class="m5-button m5-button--primary"
                        href="{{ route('staf.booking.create') }}"
                    >
                        Buat booking walk-in
                    </a>

                </div>

            @endif

        </section>

    </section>

</x-app-layout>