<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tugas Groomer</title>
</head>
<body>
    <h1>Halaman Tugas Groomer</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <hr>

    <h2>Daftar Tugas</h2>

    @forelse ($appointments as $appointment)
        <div>
            <h3>{{ $appointment->pet->nama }}</h3>

            <p>
                Pemilik:
                {{ $appointment->pet->customer->nama }}
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
                Durasi:
                {{ $appointment->durasi_estimasi }} menit
            </p>

            <p>Status: {{ $appointment->status }}</p>

            <p>Layanan:</p>
            <ul>
                @foreach ($appointment->services as $service)
                    <li>{{ $service->nama_layanan }}</li>
                @endforeach
            </ul>
        </div>

        <hr>
    @empty
        <p>Belum ada tugas grooming.</p>
    @endforelse

    {{ $appointments->links() }}
</body>
</html>
