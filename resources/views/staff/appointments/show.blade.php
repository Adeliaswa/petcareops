<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Appointment</title>
</head>
<body>
    <h1>Detail Appointment</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <hr>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <h2>Informasi Hewan</h2>
    <p>Nama: {{ $appointment->pet->nama }}</p>
    <p>Spesies: {{ $appointment->pet->spesies }}</p>
    <p>Ras: {{ $appointment->pet->ras ?? '-' }}</p>
    <p>Ukuran: {{ $appointment->pet->ukuran ?? '-' }}</p>
    <p>Jenis bulu: {{ $appointment->pet->jenis_bulu ?? '-' }}</p>

    <h2>Informasi Pemilik</h2>
    <p>Nama: {{ $appointment->pet->customer->nama }}</p>
    <p>Nomor WhatsApp: {{ $appointment->pet->customer->no_wa }}</p>

    <h2>Informasi Appointment</h2>
    <p>Status: {{ ucfirst($appointment->status) }}</p>
    <p>Sumber: {{ $appointment->sumber }}</p>
    <p>Mulai: {{ $appointment->mulai->format('d-m-Y H:i') }}</p>
    <p>Estimasi selesai: {{ $appointment->selesai_estimasi->format('d-m-Y H:i') }}</p>
    <p>Durasi estimasi: {{ $appointment->durasi_estimasi }} menit</p>
    <p>Catatan pelanggan: {{ $appointment->catatan_pelanggan ?? '-' }}</p>

    <p>
        Waktu mulai aktual:
        {{ $appointment->waktu_mulai_aktual?->format('d-m-Y H:i') ?? '-' }}
    </p>

    <p>
        Waktu selesai aktual:
        {{ $appointment->waktu_selesai_aktual?->format('d-m-Y H:i') ?? '-' }}
    </p>

    @if ($appointment->status === 'dibatalkan')
        <p>
            Alasan pembatalan:
            {{ $appointment->alasan_batal ?? '-' }}
        </p>
    @endif

    <h2>Groomer</h2>
    <p>{{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}</p>

    <h2>Layanan</h2>
    <ul>
        @forelse ($appointment->services as $service)
            <li>
                {{ $service->nama_layanan }}
                — Rp{{ number_format((float) $service->harga, 0, ',', '.') }}
            </li>
        @empty
            <li>Belum ada layanan.</li>
        @endforelse
    </ul>

    @if ($appointment->status === 'menunggu')
        <hr>

        <h2>Batalkan Appointment</h2>

        <form
            method="POST"
            action="{{ route('staf.appointments.cancel', $appointment) }}"
        >
            @csrf

            <label for="alasan_batal">Alasan pembatalan:</label>
            <input
                type="text"
                id="alasan_batal"
                name="alasan_batal"
                maxlength="255"
                value="{{ old('alasan_batal') }}"
                required
            >

            <button type="submit">Batalkan Appointment</button>
        </form>
    @endif

    <hr>

    <a href="{{ route('staf.appointments.index') }}">
        Kembali ke daftar appointment
    </a>
</body>
</html>