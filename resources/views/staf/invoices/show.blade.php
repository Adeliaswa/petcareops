
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->nomor_invoice }}</title>
</head>
<body>
    <h1>Invoice Grooming</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>Nomor Invoice: {{ $invoice->nomor_invoice }}</p>
    <p>Tanggal: {{ $invoice->diterbitkan_pada->format('d-m-Y H:i') }}</p>
    <p>Pelanggan: {{ $invoice->appointment->pet->customer->nama }}</p>
    <p>Hewan: {{ $invoice->appointment->pet->nama }}</p>

    <h2>Rincian Layanan</h2>
    <table border="1" cellpadding="8">
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
    <p>Total Dibayar: Rp{{ number_format($invoice->total_dibayar, 0, ',', '.') }}</p>
    <p>Sisa Tagihan: Rp{{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}</p>
    <p>Status: {{ $invoice->status_pembayaran }}</p>

    <h2>Riwayat Pembayaran</h2>
    @forelse ($invoice->payments as $payment)
        <div>
            <p>
                {{ ucfirst($payment->metode) }} -
                Rp{{ number_format($payment->nominal, 0, ',', '.') }}
                ({{ $payment->dibayar_pada->format('d-m-Y H:i') }})
            </p>

            @if (auth()->user()->hasRole('admin'))
                <form method="POST"
                      action="{{ route('staf.payments.destroy', $payment) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus Pembayaran</button>
                </form>
            @endif
        </div>
    @empty
        <p>Belum ada pembayaran.</p>
    @endforelse

    <h2>Catat Pembayaran</h2>

    @if ($invoice->sisa_tagihan > 0)
        <form method="POST"
              action="{{ route('staf.invoices.payments.store', $invoice) }}">
            @csrf

            <label>Metode pembayaran</label>
            <select name="metode" required>
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
            </select>

            <br><br>

            <label>Nominal pembayaran</label>
            <input type="number" name="nominal"
                   min="1" max="{{ $invoice->sisa_tagihan }}" required>

            <br><br>

            <label>Tanggal pembayaran</label>
            <input type="datetime-local" name="dibayar_pada">

            <br><br>

            <button type="submit">Simpan Pembayaran</button>
        </form>
    @else
        <p>Invoice sudah lunas.</p>
    @endif

    <p>
        <a href="{{ route('staf.invoices.print', $invoice) }}">
            Lihat halaman cetak invoice
        </a>
    </p>
</body>
</html>
