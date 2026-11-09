
<x-app-layout>
    <x-slot name="header">
        Appointment
    </x-slot>

    @php
        $totalAppointments = $appointments->total();
        $firstItem = $appointments->firstItem() ?? 0;
        $lastItem = $appointments->lastItem() ?? 0;
    @endphp

    <section class="appointments-page">

        {{-- Page introduction --}}
        <div class="appointments-intro">
            <div>
                <span class="appointments-eyebrow">
                    OPERASIONAL
                </span>

                <h1 class="appointments-title">
                    Daftar Appointment
                </h1>

                <p class="appointments-description">
                    Pantau dan kelola jadwal janji temu pelanggan
                    di Pet Care Ops.
                </p>
            </div>

            <a href="{{ route('staf.booking.create') }}"
               class="appointments-add-button">
                <x-icon name="plus" />
                <span>Buat Appointment</span>
            </a>
        </div>

        {{-- Summary --}}
        <div class="appointments-summary">

            <article class="appointments-summary-card">
                <div class="appointments-summary-icon appointments-summary-icon--brown">
                    <x-icon name="calendar-days" />
                </div>

                <div>
                    <span class="appointments-summary-label">
                        Total Appointment
                    </span>

                    <strong class="appointments-summary-value">
                        {{ number_format($totalAppointments, 0, ',', '.') }}
                    </strong>

                    <span class="appointments-summary-note">
                        Seluruh data appointment
                    </span>
                </div>
            </article>

            <article class="appointments-summary-card">
                <div class="appointments-summary-icon appointments-summary-icon--pink">
                    <x-icon name="clipboard-list" />
                </div>

                <div>
                    <span class="appointments-summary-label">
                        Data Ditampilkan
                    </span>

                    <strong class="appointments-summary-value">
                        {{ $appointments->count() }}
                    </strong>

                    <span class="appointments-summary-note">
                        Pada halaman ini
                    </span>
                </div>
            </article>

            <article class="appointments-summary-card">
                <div class="appointments-summary-icon appointments-summary-icon--sage">
                    <x-icon name="calendar-check" />
                </div>

                <div>
                    <span class="appointments-summary-label">
                        Halaman
                    </span>

                    <strong class="appointments-summary-value">
                        {{ $appointments->currentPage() }}
                    </strong>

                    <span class="appointments-summary-note">
                        Dari {{ $appointments->lastPage() }} halaman
                    </span>
                </div>
            </article>

        </div>

        {{-- Appointment table --}}
        <div class="appointments-panel">

            <div class="appointments-panel-header">
                <div>
                    <h2 class="appointments-panel-title">
                        Semua Appointment
                    </h2>

                    <p class="appointments-panel-description">
                        Daftar janji temu yang tersimpan dalam sistem.
                    </p>
                </div>

                <span class="appointments-total-badge">
                    {{ number_format($totalAppointments, 0, ',', '.') }} data
                </span>
            </div>

            @if ($appointments->isNotEmpty())

                <div class="appointments-table-wrapper">

                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Appointment</th>
                                <th>Jadwal</th>
                                <th>Pemilik & Hewan</th>
                                <th>Layanan</th>
                                <th>Groomer</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($appointments as $appointment)

                                @php
                                    $status = strtolower($appointment->status ?? '');
                                    $statusClass = \Illuminate\Support\Str::slug($status);
                                @endphp

                                <tr>
                                    {{-- Appointment ID --}}
                                    <td>
                                        <span class="appointments-code">
                                            #{{ $appointment->id }}
                                        </span>
                                    </td>

                                    {{-- Schedule --}}
                                    <td>
                                        <div class="appointments-date">
                                            @if ($appointment->mulai)
                                                {{ \Illuminate\Support\Carbon::parse($appointment->mulai)->format('d M Y') }}
                                                <span>
                                                    {{ \Illuminate\Support\Carbon::parse($appointment->mulai)->format('H:i') }} WIB
                                                </span>
                                            @else
                                                <span class="appointments-muted">Belum ditentukan</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Customer and pet --}}
                                    <td>
                                        <div class="appointments-person">
                                            <strong>
                                                {{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}
                                            </strong>

                                            <span>
                                                {{ $appointment->pet?->nama ?? 'Hewan tidak tersedia' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Services --}}
                                    <td>
                                        <div class="appointments-services">
                                            @forelse ($appointment->services as $service)
                                                <span class="appointments-service-tag">
                                                    {{ $service->nama }}
                                                </span>
                                            @empty
                                                <span class="appointments-muted">
                                                    Belum ada layanan
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Groomer --}}
                                    <td>
                                        <span class="appointments-groomer">
                                            {{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span class="appointments-status appointments-status--{{ $statusClass ?: 'unknown' }}">
                                            {{ $appointment->status
                                                ? \Illuminate\Support\Str::headline($appointment->status)
                                                : 'Tidak diketahui' }}
                                        </span>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>

                </div>

                {{-- Pagination --}}
                <div class="appointments-pagination">

                    <span class="appointments-pagination-info">
                        Menampilkan {{ $firstItem }}–{{ $lastItem }}
                        dari {{ number_format($totalAppointments, 0, ',', '.') }} appointment
                    </span>

                    <div>
                        {{ $appointments->links() }}
                    </div>

                </div>

            @else

                {{-- Empty state --}}
                <div class="appointments-empty">

                    <div class="appointments-empty-icon">
                        <x-icon name="calendar-days" />
                    </div>

                    <h3>Belum Ada Appointment</h3>

                    <p>
                        Belum ada janji temu yang tercatat.
                        Buat appointment baru untuk memulai.
                    </p>

                    <a href="{{ route('staf.booking.create') }}"
                       class="appointments-add-button">
                        <x-icon name="plus" />
                        Buat Appointment
                    </a>

                </div>

            @endif

        </div>

    </section>
</x-app-layout>
