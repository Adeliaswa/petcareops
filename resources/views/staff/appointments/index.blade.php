<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janji Temu Staff</title>
</head>
<body>
    <h1>Daftar Janji Temu</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <hr>

    <h2>Pencarian dan Filter</h2>

    <form method="GET">
        <div>
            <label for="search">Cari hewan atau pemilik:</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Nama hewan atau pemilik"
            >
        </div>

        <div>
            <label for="status">Status:</label>
            <select name="status" id="status">
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
                        @selected($status === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tanggal">Tanggal:</label>
            <input
                type="date"
                id="tanggal"
                name="tanggal"
                value="{{ $tanggal }}"
            >
        </div>

        <button type="submit">Terapkan Filter</button>
        <a href="{{ url()->current() }}">Reset</a>
    </form>

    <hr>

    <h2>Data Appointment</h2>

    <p>Total data pada halaman ini: {{ $appointments->count() }}</p>

    @forelse ($appointments as $appointment)
        <article>
            <h3>{{ $appointment->pet->nama }}</h3>

            <p>
                Pemilik:
                {{ $appointment->pet->customer->nama }}
            </p>

            <p>
                Groomer:
                {{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}
            </p>

            <p>
                Mulai:
                {{ $appointment->mulai->format('d-m-Y H:i') }}
            </p>

            <p>
                Estimasi selesai:
                {{ $appointment->selesai_estimasi->format('d-m-Y H:i') }}
            </p>

            <p>
                Durasi estimasi:
                {{ $appointment->durasi_estimasi }} menit
            </p>

            <p>Status: {{ ucfirst($appointment->status) }}</p>

            <p>Layanan:</p>
            <ul>
                @forelse ($appointment->services as $service)
                    <li>{{ $service->nama_layanan }}</li>
                @empty
                    <li>Belum ada layanan</li>
                @endforelse
            </ul>

            <p>Sumber booking: {{ $appointment->sumber }}</p>
            <a href="{{ route('staf.appointments.show', $appointment) }}">
    Lihat Detail
</a>
        </article>

        <hr>
    @empty
        <p>Tidak ada appointment yang sesuai dengan pencarian atau filter.</p>
    @endforelse

    {{ $appointments->links() }}
</body>
</html>