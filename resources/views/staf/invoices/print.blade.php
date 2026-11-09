
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->nomor_invoice }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 30px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .actions {
            margin-bottom: 20px;
        }

        @media print {
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Cetak Invoice</button>
    </div>

    <h1>Invoice Grooming</h1>

    <p>Nomor: {{ $invoice->nomor_invoice }}</p>
    <p>Tanggal: {{ $invoice->diterbitkan_pada->format('d-m-Y H:i') }}</p>
    <p>Pelanggan: {{ $invoice->appointment->pet->customer->nama }}</p>
    <p>Hewan: {{ $invoice->appointment->pet->nama }}</p>

    <h2>Rincian Layanan</h2>

    <table>
        <thead>
            <tr>
                <th>Layanan</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->appointment->services as $service)
                <tr>
                    <td>{{ $service->nama_layanan }}</td>
                    <td>Rp{{ number_format($service->harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: Rp{{ number_format($invoice->total, 0, ',', '.') }}</h3>
    <p>Total dibayar: Rp{{ number_format($invoice->total_dibayar, 0, ',', '.') }}</p>
    <p>Sisa tagihan: Rp{{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}</p>
    <p>Status pembayaran: {{ $invoice->status_pembayaran }}</p>
</body>
</html>
