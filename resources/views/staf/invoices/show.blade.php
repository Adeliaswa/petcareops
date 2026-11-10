
<x-app-layout>
    <x-slot name="header">
        <h1 class="m5-page-title">Invoice & Pembayaran</h1>
    </x-slot>

    @php
        $appointment = $invoice->appointment;
        $pet = $appointment?->pet;
        $customer = $pet?->customer;

        $statusLabel = [
            'belum_bayar' => 'Belum dibayar',
            'sebagian' => 'Dibayar sebagian',
            'lunas' => 'Lunas',
        ][$invoice->status_pembayaran] ?? ucfirst(
            $invoice->status_pembayaran
        );
    @endphp

    <section class="m6-page m6-invoice-page">

        {{-- BACK --}}
        <a class="m5-back-link"
           href="{{ route('staf.appointments.show', $appointment) }}">
            ← Kembali ke detail appointment
        </a>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="m5-alert m5-alert--success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="m5-alert m5-alert--error" role="alert">
                <strong>Pembayaran belum berhasil disimpan.</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="m6-heading">

            <div>
                <span class="m5-eyebrow">TAGIHAN PELANGGAN</span>

                <h2>Invoice Grooming</h2>

                <p>
                    Rincian layanan dan pencatatan pembayaran appointment.
                </p>
            </div>

            <a class="m5-button m5-button--primary"
               href="{{ route('staf.invoices.print', $invoice) }}"
               target="_blank"
               rel="noopener">

                <span aria-hidden="true">⤓</span>
                Cetak invoice

            </a>

        </div>

        <div class="m6-invoice-layout">

            {{-- KOLOM KIRI --}}
            <div class="m6-invoice-main">

                {{-- DETAIL INVOICE --}}
                <section class="m5-panel m6-invoice-paper">

                    {{-- BRAND --}}
                    <div class="m6-invoice-brand">

                        <div class="m6-brand-mark">
                            PC
                        </div>

                        <div>
                            <strong>Pet Care Ops</strong>
                            <span>Pet Grooming &amp; Spa</span>
                        </div>

                        <div class="m6-invoice-tag">
                            INVOICE
                        </div>

                    </div>

                    {{-- NOMOR INVOICE --}}
                    <div class="m6-invoice-number">

                        <div>
                            <small>Nomor invoice</small>

                            <strong>
                                {{ $invoice->nomor_invoice }}
                            </strong>
                        </div>

                        <div>
                            <small>Tanggal diterbitkan</small>

                            <strong>
                                {{ $invoice->diterbitkan_pada?->translatedFormat('d F Y, H:i') ?? '—' }}
                            </strong>
                        </div>

                    </div>

                    {{-- IDENTITAS --}}
                    <div class="m6-invoice-parties">

                        <div>
                            <small>Ditagihkan kepada</small>

                            <strong>
                                {{ $customer?->nama ?? '—' }}
                            </strong>

                            <span>
                                {{ $customer?->no_wa ?? 'Kontak tidak tersedia' }}
                            </span>
                        </div>

                        <div>
                            <small>Detail hewan</small>

                            <strong>
                                {{ $pet?->nama ?? '—' }}
                            </strong>

                            <span>
                                {{ ucfirst($pet?->spesies ?? 'Hewan') }}

                                {{ $pet?->ras ? ' · '.$pet->ras : '' }}
                            </span>
                        </div>

                    </div>

                    {{-- RINCIAN LAYANAN --}}
                    <div class="m5-table-wrap">

                        <table class="m5-table m6-invoice-table">

                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th>Durasi dasar</th>
                                    <th class="m6-align-right">
                                        Harga
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($appointment?->services ?? [] as $service)

                                    <tr>

                                        <td>
                                            <strong>
                                                {{ $service->nama_layanan }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $service->durasi_dasar ?? '—' }}

                                            {{ $service->durasi_dasar ? ' menit' : '' }}
                                        </td>

                                        <td class="m6-align-right">
                                            Rp
                                            {{ number_format(
                                                (float) $service->harga,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="3">
                                            Tidak ada rincian layanan.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- TOTAL --}}
                    <div class="m6-invoice-total">

                        <span>Total tagihan</span>

                        <strong>
                            Rp {{ number_format(
                                $invoice->total,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>

                    </div>

                    {{-- STATUS --}}
                    <div class="m6-invoice-status-line">

                        <span>Status pembayaran</span>

                        <span class="m6-status m6-status--{{ $invoice->status_pembayaran }}">
                            {{ $statusLabel }}
                        </span>

                    </div>

                </section>

                {{-- RIWAYAT PEMBAYARAN --}}
                <section class="m5-panel">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Riwayat pembayaran</h3>

                            <p>
                                Setiap pembayaran yang sudah dicatat
                                untuk invoice ini.
                            </p>
                        </div>

                        <span class="m5-count">
                            {{ $invoice->payments->count() }} transaksi
                        </span>

                    </div>

                    @if ($invoice->payments->count())

                        <div class="m5-table-wrap">

                            <table class="m5-table">

                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Metode</th>
                                        <th>Dicatat oleh</th>

                                        <th class="m6-align-right">
                                            Nominal
                                        </th>

                                        @if (auth()->user()->hasRole('admin'))
                                            <th>Aksi</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($invoice->payments as $payment)

                                        <tr>

                                            <td>
                                                {{ $payment->dibayar_pada?->translatedFormat('d M Y, H:i') ?? '—' }}
                                            </td>

                                            <td>
                                                <span class="m6-method">
                                                    {{ strtoupper($payment->metode) }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $payment->pencatat?->name ?? '—' }}
                                            </td>

                                            <td class="m6-align-right">
                                                <strong>
                                                    Rp
                                                    {{ number_format(
                                                        $payment->nominal,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </strong>
                                            </td>

                                            @if (auth()->user()->hasRole('admin'))

                                                <td>

                                                    <form method="POST"
                                                          action="{{ route('staf.payments.destroy', $payment) }}"
                                                          onsubmit="return confirm('Hapus catatan pembayaran ini? Data akan diarsipkan dan status tagihan dihitung ulang.')">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button class="m6-delete-link"
                                                                type="submit">
                                                            Hapus
                                                        </button>

                                                    </form>

                                                </td>

                                            @endif

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="m5-empty m5-empty--compact">

                            <span class="m5-empty-icon">↗</span>

                            <h3>Belum ada pembayaran</h3>

                            <p>
                                Transaksi yang dicatat akan muncul di sini.
                            </p>

                        </div>

                    @endif

                </section>

            </div>

            {{-- KOLOM KANAN --}}
            <aside class="m6-payment-aside">

                {{-- RINGKASAN TAGIHAN --}}
                <section class="m5-panel m6-balance-card">

                    <span class="m5-eyebrow">
                        RINGKASAN TAGIHAN
                    </span>

                    <div>
                        <small>Total tagihan</small>

                        <strong>
                            Rp {{ number_format(
                                $invoice->total,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div>
                        <small>Sudah dibayar</small>

                        <strong class="m6-paid">
                            Rp {{ number_format(
                                $invoice->total_dibayar,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </div>

                    <div class="m6-balance-due">

                        <small>Sisa tagihan</small>

                        <strong>
                            Rp {{ number_format(
                                $invoice->sisa_tagihan,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>

                    </div>

                    @php
                        $percent = $invoice->total > 0
                            ? min(
                                100,
                                (
                                    $invoice->total_dibayar /
                                    $invoice->total
                                ) * 100
                            )
                            : 0;
                    @endphp

                    <div class="m6-progress">
                        <span style="width: {{ $percent }}%"></span>
                    </div>

                    <small class="m6-progress-label">
                        {{ number_format($percent, 0) }}% telah dibayar
                    </small>

                </section>

                {{-- FORM PEMBAYARAN --}}
                <section class="m5-panel m6-payment-form">

                    <div class="m5-panel-heading">

                        <div>
                            <h3>Catat pembayaran</h3>

                            <p>
                                Masukkan pembayaran yang diterima kasir.
                            </p>
                        </div>

                    </div>

                    @if ($invoice->sisa_tagihan > 0)

                        <form method="POST"
                              action="{{ route('staf.invoices.payments.store', $invoice) }}"
                              class="m6-form"
                              data-payment-form>

                            @csrf

                            {{-- METODE --}}
                            <label class="m5-field">

                                <span>Metode pembayaran</span>

                                <select name="metode" required>

                                    <option value="tunai"
                                        @selected(old('metode') === 'tunai')>
                                        Tunai
                                    </option>

                                    <option value="transfer"
                                        @selected(old('metode') === 'transfer')>
                                        Transfer
                                    </option>

                                    <option value="qris"
                                        @selected(old('metode') === 'qris')>
                                        QRIS
                                    </option>

                                </select>

                            </label>

                            {{-- NOMINAL --}}
                            <label class="m5-field">

                                <span>Nominal pembayaran (Rp)</span>

                                <input
                                    type="number"
                                    name="nominal"
                                    min="1"
                                    max="{{ $invoice->sisa_tagihan }}"
                                    step="1"
                                    value="{{ old('nominal') }}"
                                    placeholder="Contoh: 150000"
                                    required
                                >

                                <small>
                                    Sisa tagihan maksimal
                                    Rp {{ number_format(
                                        $invoice->sisa_tagihan,
                                        0,
                                        ',',
                                        '.'
                                    ) }}.
                                </small>

                            </label>

                            {{-- TANGGAL --}}
                            <label class="m5-field">

                                <span>Waktu pembayaran</span>

                                <input
                                    type="datetime-local"
                                    name="dibayar_pada"
                                    value="{{ old('dibayar_pada') }}"
                                >

                            </label>

                            {{-- SUBMIT --}}
                            <button
                                class="m5-button m5-button--primary m5-button--full"
                                type="submit"
                                data-submit-payment
                            >
                                Simpan pembayaran
                            </button>

                            <p class="m6-form-note">
                                Pembayaran dapat dicatat bertahap.
                                Total pembayaran tidak dapat melebihi tagihan.
                            </p>

                        </form>

                    @else

                        <div class="m6-paid-state">

                            <span>✓</span>

                            <strong>Tagihan sudah lunas</strong>

                            <p>
                                Tidak ada sisa pembayaran
                                yang perlu dicatat.
                            </p>

                        </div>

                    @endif

                </section>

            </aside>

        </div>

    </section>

    {{-- MENCEGAH DOUBLE SUBMIT --}}
    <script>
        document.querySelectorAll('[data-payment-form]').forEach(form => {

            form.addEventListener('submit', () => {

                const button = form.querySelector(
                    '[data-submit-payment]'
                );

                if (button) {
                    button.disabled = true;
                    button.textContent = 'Menyimpan…';
                }

            });

        });
    </script>

</x-app-layout>