
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        Invoice {{ $invoice->nomor_invoice }} · Pet Care Ops
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        :root {
            --brown: #533e36;
            --muted: #8b7d76;
            --line: #eadfd9;
            --pink: #ffc5be;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 32px;
            background: #f7f3f0;
            color: var(--brown);
            font: 14px/1.55 "Plus Jakarta Sans", sans-serif;
        }

        /* TOOLBAR */

        .invoice-toolbar {
            max-width: 820px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .invoice-toolbar a,
        .invoice-toolbar button {
            border: 0;
            border-radius: 9px;
            padding: 11px 16px;
            background: var(--brown);
            color: white;
            text-decoration: none;
            font: 600 13px "Plus Jakarta Sans", sans-serif;
            cursor: pointer;
            transition: .2s;
        }

        .invoice-toolbar a:hover,
        .invoice-toolbar button:hover {
            background: #6a5148;
        }

        /* INVOICE PAPER */

        .invoice-sheet {
            max-width: 820px;
            margin: auto;
            padding: 48px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 12px 40px #533e3610;
        }

        /* BRAND */

        .invoice-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--line);
        }

        .invoice-brand-mark {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #fff0ed;
            font: 700 17px Fredoka, sans-serif;
        }

        .invoice-brand strong {
            display: block;
            font: 600 22px Fredoka, sans-serif;
        }

        .invoice-brand small {
            color: var(--muted);
        }

        .invoice-title {
            margin-left: auto;
            font: 700 22px Fredoka, sans-serif;
            letter-spacing: .08em;
        }

        /* INVOICE INFORMATION */

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 0;
        }

        .invoice-meta small,
        .invoice-party small {
            display: block;
            color: var(--muted);
            font-size: 11px;
        }

        .invoice-meta strong,
        .invoice-party strong {
            display: block;
            margin-top: 4px;
        }

        /* CUSTOMER INFORMATION */

        .invoice-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 15px 0 28px;
        }

        /* TABLE */

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th {
            background: #faf6f3;
            color: var(--muted);
            font-size: 11px;
            text-align: left;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--line);
        }

        .invoice-table .right {
            text-align: right;
        }

        /* TOTAL */

        .invoice-total {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 40px;
            padding: 22px 14px;
            border-bottom: 1px solid var(--line);
        }

        .invoice-total strong {
            font: 600 22px Fredoka, sans-serif;
        }

        /* PAYMENT SUMMARY */

        .invoice-summary {
            width: min(330px, 100%);
            margin: 20px 0 0 auto;
        }

        .invoice-summary div {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 0;
        }

        .invoice-summary .due {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 1px solid var(--line);
            font-weight: 700;
        }

        /* FOOTER */

        .invoice-footer {
            margin-top: 40px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
            text-align: center;
        }

        /* RESPONSIVE */

        @media (max-width: 600px) {

            body {
                padding: 12px;
            }

            .invoice-sheet {
                padding: 22px 16px;
            }

            .invoice-meta {
                flex-direction: column;
            }

            .invoice-toolbar {
                flex-wrap: wrap;
            }

            .invoice-parties {
                gap: 10px;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 9px 7px;
                font-size: 12px;
            }
        }

        /* PRINT */

        @media print {

            body {
                padding: 0;
                background: #fff;
            }

            .invoice-sheet {
                max-width: none;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }

            .invoice-toolbar {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 16mm;
            }
        }

    </style>

</head>

<body>

    @php
        $appointment = $invoice->appointment;
        $pet = $appointment?->pet;
        $customer = $pet?->customer;
    @endphp

    {{-- TOOLBAR --}}
    <div class="invoice-toolbar">

        <a href="{{ route('staf.invoices.show', $invoice) }}">
            ← Kembali ke invoice
        </a>

        <button type="button" onclick="window.print()">
            Cetak / Simpan PDF
        </button>

    </div>

    {{-- INVOICE --}}
    <main class="invoice-sheet">

        {{-- HEADER BRAND --}}
        <header class="invoice-brand">

            <div class="invoice-brand-mark">
                PC
            </div>

            <div>
                <strong>Pet Care Ops</strong>
                <small>Pet Grooming &amp; Spa</small>
            </div>

            <div class="invoice-title">
                INVOICE
            </div>

        </header>

        {{-- NOMOR DAN TANGGAL --}}
        <section class="invoice-meta">

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

        </section>

        {{-- PELANGGAN DAN HEWAN --}}
        <section class="invoice-parties">

            <div class="invoice-party">

                <small>Ditagihkan kepada</small>

                <strong>
                    {{ $customer?->nama ?? '—' }}
                </strong>

                <span>
                    {{ $customer?->no_wa ?? '—' }}
                </span>

            </div>

            <div class="invoice-party">

                <small>Detail hewan</small>

                <strong>
                    {{ $pet?->nama ?? '—' }}
                </strong>

                <span>
                    {{ ucfirst($pet?->spesies ?? 'Hewan') }}

                    {{ $pet?->ras ? ' · '.$pet->ras : '' }}
                </span>

            </div>

        </section>

        {{-- RINCIAN LAYANAN --}}
        <table class="invoice-table">

            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Durasi dasar</th>
                    <th class="right">Harga</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($appointment?->services ?? [] as $service)

                    <tr>

                        <td>
                            {{ $service->nama_layanan }}
                        </td>

                        <td>
                            {{ $service->durasi_dasar ?? '—' }}

                            {{ $service->durasi_dasar ? ' menit' : '' }}
                        </td>

                        <td class="right">
                            Rp {{ number_format(
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

        {{-- TOTAL --}}
        <div class="invoice-total">

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

        {{-- RINGKASAN PEMBAYARAN --}}
        <section class="invoice-summary">

            <div>
                <span>Total dibayar</span>

                <strong>
                    Rp {{ number_format(
                        $invoice->total_dibayar,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>
            </div>

            <div class="due">

                <span>Sisa tagihan</span>

                <strong>
                    Rp {{ number_format(
                        $invoice->sisa_tagihan,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

            <div>

                <span>Status</span>

                <strong>
                    {{ [
                        'belum_bayar' => 'Belum dibayar',
                        'sebagian' => 'Dibayar sebagian',
                        'lunas' => 'Lunas'
                    ][$invoice->status_pembayaran] ?? $invoice->status_pembayaran }}
                </strong>

            </div>

        </section>

        {{-- RIWAYAT PEMBAYARAN --}}
        @if ($invoice->payments->count())

            <h3 style="margin:30px 0 8px;font:600 16px Fredoka,sans-serif;">
                Riwayat pembayaran
            </h3>

            <table class="invoice-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th class="right">Nominal</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($invoice->payments as $payment)

                        <tr>

                            <td>
                                {{ $payment->dibayar_pada?->translatedFormat('d M Y, H:i') ?? '—' }}
                            </td>

                            <td>
                                {{ strtoupper($payment->metode) }}
                            </td>

                            <td class="right">
                                Rp {{ number_format(
                                    $payment->nominal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @endif

        {{-- FOOTER --}}
        <footer class="invoice-footer">

            Terima kasih telah mempercayakan perawatan
            hewan kesayangan Anda kepada kami.

        </footer>

    </main>

</body>
</html>