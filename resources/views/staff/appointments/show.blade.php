
<x-app-layout>

    <x-slot name="header">
        <h1 class="m5-page-title">Detail Appointment</h1>
    </x-slot>

    @php
        $status = $appointment->status;

        $record = $appointment->groomingRecord;

        $estimasi = (int) $appointment->durasi_estimasi;

        $aktual = $record?->durasi_aktual;

        $selisih = $aktual !== null
            ? $aktual - $estimasi
            : null;

        $invoice = $appointment->invoice;

        $invoiceStatusLabel = $invoice
            ? [
                'belum_bayar' => 'Belum dibayar',
                'sebagian' => 'Dibayar sebagian',
                'lunas' => 'Lunas',
            ][$invoice->status_pembayaran] ?? ucfirst(
                $invoice->status_pembayaran
            )
            : null;
    @endphp

    <section class="m5-page m5-detail-page">

        {{-- BACK --}}
        <a class="m5-back-link"
           href="{{ route('staf.appointments.index') }}">
            ← Kembali ke daftar appointment
        </a>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="m5-alert m5-alert--success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="m5-alert m5-alert--error">

                <strong>Periksa kembali data berikut:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        {{-- HEADER DETAIL --}}
        <div class="m5-detail-heading">

            <div>

                <span class="m5-eyebrow">
                    APPOINTMENT #{{ $appointment->id }}
                </span>

                <h2>
                    {{ $appointment->pet?->nama ?? 'Data hewan' }}
                </h2>

                <p>
                    {{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}
                </p>

            </div>

            <span class="m5-status m5-status--{{ $status }}">
                {{ ucfirst($status) }}
            </span>

        </div>

        <div class="m5-detail-grid">

            {{-- KOLOM KIRI --}}
            <div class="m5-detail-main">

                {{-- INFORMASI JADWAL --}}
                <section class="m5-panel">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Informasi jadwal</h3>

                            <p>
                                Waktu booking dan penugasan grooming.
                            </p>
                        </div>

                    </div>

                    <dl class="m5-info-list">

                        <div>
                            <dt>Tanggal</dt>

                            <dd>
                                {{ $appointment->mulai->translatedFormat('l, d F Y') }}
                            </dd>
                        </div>

                        <div>
                            <dt>Waktu mulai</dt>

                            <dd>
                                {{ $appointment->mulai->format('H:i') }} WIB
                            </dd>
                        </div>

                        <div>
                            <dt>Estimasi selesai</dt>

                            <dd>
                                {{ $appointment->selesai_estimasi->format('H:i') }} WIB
                            </dd>
                        </div>

                        <div>
                            <dt>Groomer</dt>

                            <dd>
                                {{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Sumber booking</dt>

                            <dd>
                                {{ $appointment->sumber === 'walk-in' ? 'Walk-in' : ucfirst($appointment->sumber) }}
                            </dd>
                        </div>

                    </dl>

                </section>

                {{-- INFORMASI HEWAN DAN PEMILIK --}}
                <section class="m5-panel">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Hewan dan pemilik</h3>

                            <p>
                                Informasi yang menyertai appointment.
                            </p>
                        </div>

                    </div>

                    <dl class="m5-info-list">

                        <div>
                            <dt>Nama hewan</dt>

                            <dd>
                                {{ $appointment->pet?->nama ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Spesies / ras</dt>

                            <dd>
                                {{ ucfirst($appointment->pet?->spesies ?? '-') }}

                                {{ $appointment->pet?->ras
                                    ? ' / '.$appointment->pet->ras
                                    : '' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Ukuran</dt>

                            <dd>
                                {{ $appointment->pet?->ukuran ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Jenis bulu</dt>

                            <dd>
                                {{ $appointment->pet?->jenis_bulu ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Perilaku</dt>

                            <dd>
                                {{ $appointment->pet?->perilaku ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Alergi</dt>

                            <dd>
                                {{ $appointment->pet?->alergi ?: 'Tidak ada catatan' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Catatan kesehatan</dt>

                            <dd>
                                {{ $appointment->pet?->catatan_kesehatan ?: 'Tidak ada catatan' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Catatan pelanggan</dt>

                            <dd>
                                {{ $appointment->catatan_pelanggan ?: 'Tidak ada catatan' }}
                            </dd>
                        </div>

                    </dl>

                </section>

                {{-- LAYANAN --}}
                <section class="m5-panel">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Layanan yang dipilih</h3>

                            <p>
                                Harga merupakan snapshot saat booking dibuat.
                            </p>
                        </div>

                    </div>

                    <div class="m5-service-list">

                        @forelse ($appointment->services as $service)

                            <div class="m5-service-row">

                                <div>
                                    <strong>
                                        {{ $service->nama_layanan }}
                                    </strong>

                                    <span>
                                        {{ $service->durasi_dasar }} menit
                                    </span>
                                </div>

                                <strong>
                                    Rp{{ number_format(
                                        (float) $service->harga,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>

                        @empty

                            <p class="m5-muted">
                                Belum ada layanan pada appointment ini.
                            </p>

                        @endforelse

                    </div>

                </section>

                {{-- DURASI GROOMING --}}
                <section class="m5-panel">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Durasi grooming</h3>

                            <p>
                                Perbandingan estimasi dan hasil aktual.
                            </p>
                        </div>

                    </div>

                    <div class="m5-duration-grid">

                        <div>
                            <span>Estimasi</span>

                            <strong>
                                {{ $estimasi }}
                                <small>menit</small>
                            </strong>
                        </div>

                        <div>
                            <span>Aktual</span>

                            <strong>
                                {{ $aktual !== null ? $aktual : '—' }}

                                @if ($aktual !== null)
                                    <small>menit</small>
                                @endif
                            </strong>
                        </div>

                        <div>
                            <span>Selisih</span>

                            <strong>
                                {{ $selisih !== null
                                    ? ($selisih > 0 ? '+' : '').$selisih.' menit'
                                    : '—' }}
                            </strong>
                        </div>

                    </div>

                    @if ($record?->catatan_grooming)

                        <div class="m5-note">

                            <strong>Catatan perawatan</strong>

                            <p>
                                {{ $record->catatan_grooming }}
                            </p>

                        </div>

                    @endif

                    <dl class="m5-info-list m5-info-list--compact">

                        <div>
                            <dt>Mulai aktual</dt>

                            <dd>
                                {{ $appointment->waktu_mulai_aktual?->format('d M Y, H:i') ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt>Selesai aktual</dt>

                            <dd>
                                {{ $appointment->waktu_selesai_aktual?->format('d M Y, H:i') ?? '—' }}
                            </dd>
                        </div>

                    </dl>

                </section>

            </div>

            {{-- KOLOM KANAN --}}
            <aside class="m5-detail-aside">

                {{-- INVOICE DAN PEMBAYARAN M6 --}}
                @if (in_array($status, ['selesai', 'dijemput']))

                    <section class="m5-panel m6-appointment-invoice">

                        <span class="m5-eyebrow">
                            KEUANGAN
                        </span>

                        <h3>Invoice &amp; pembayaran</h3>

                        @if ($invoice)

                            {{-- INVOICE SUDAH ADA --}}
                            <div class="m6-appointment-balance">

                                <span class="m6-status m6-status--{{ $invoice->status_pembayaran }}">

                                    {{ $invoiceStatusLabel }}

                                </span>

                                <strong>
                                    Rp{{ number_format(
                                        $invoice->sisa_tagihan,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                                <small>
                                    Sisa tagihan
                                </small>

                            </div>

                            <div class="m6-invoice-mini-info">

                                <span>Nomor invoice</span>

                                <strong>
                                    {{ $invoice->nomor_invoice }}
                                </strong>

                                <span>Total tagihan</span>

                                <strong>
                                    Rp{{ number_format(
                                        $invoice->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>

                            <a
                                class="m5-button m5-button--primary m5-button--full"
                                href="{{ route('staf.invoices.show', $invoice) }}"
                            >
                                Lihat invoice
                            </a>

                        @else

                            {{-- BELUM ADA INVOICE --}}
                            <p>
                                Appointment sudah selesai.
                                Buat invoice untuk mulai mencatat
                                pembayaran pelanggan.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('staf.appointments.invoice.store', $appointment) }}"
                            >

                                @csrf

                                <button
                                    class="m5-button m5-button--primary m5-button--full"
                                    type="submit"
                                >
                                    Buat invoice
                                </button>

                            </form>

                        @endif

                    </section>

                @endif

                {{-- AKSI APPOINTMENT M5 --}}
                <section class="m5-panel m5-action-panel">

                    <h3>Aksi appointment</h3>

                    @if ($status === 'menunggu')

                        <p>
                            Appointment masih menunggu.
                            Jika pelanggan membatalkan, isi alasan
                            sebelum melanjutkan.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('staf.appointments.cancel', $appointment) }}"
                            onsubmit="return confirm('Yakin ingin membatalkan appointment ini? Slot groomer akan dibuka kembali.')"
                        >

                            @csrf

                            <label class="m5-field">

                                <span>Alasan pembatalan</span>

                                <textarea
                                    name="alasan_batal"
                                    rows="3"
                                    maxlength="255"
                                    required
                                >{{ old('alasan_batal') }}</textarea>

                            </label>

                            <button
                                class="m5-button m5-button--danger m5-button--full"
                                type="submit"
                            >
                                Batalkan appointment
                            </button>

                        </form>

                    @elseif ($status === 'dibatalkan')

                        <div class="m5-note">

                            <strong>Appointment dibatalkan</strong>

                            <p>
                                {{ $appointment->alasan_batal ?: 'Tidak ada alasan yang dicatat.' }}
                            </p>

                        </div>

                    @elseif ($status === 'dijemput')

                        <div class="m5-note">

                            <strong>Proses selesai</strong>

                            <p>
                                Hewan telah dijemput.
                                Status ini bersifat final.
                            </p>

                        </div>

                    @else

                        <div class="m5-note">

                            <strong>Menunggu proses berikutnya</strong>

                            <p>
                                Status saat ini: {{ ucfirst($status) }}.
                                Perubahan status selain pembatalan
                                mengikuti alur operasional dan hak
                                akses yang tersedia.
                            </p>

                        </div>

                    @endif

                    <a
                        class="m5-button m5-button--light m5-button--full"
                        href="{{ route('staf.appointments.index') }}"
                    >
                        Kembali ke daftar
                    </a>

                </section>

            </aside>

        </div>

    </section>

</x-app-layout>