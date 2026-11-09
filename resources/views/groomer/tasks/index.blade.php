<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <h2>Tugas Hari Ini</h2>

    <p>Tanggal: {{ now()->translatedFormat('l, d F Y') }}</p>

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

            <p>Status: {{ ucfirst($appointment->status) }}</p>

            <p>Layanan:</p>
            <ul>
                @foreach ($appointment->services as $service)
                    <li>{{ $service->nama_layanan }}</li>
                @endforeach
            </ul>

            @if ($appointment->status === 'menunggu' && $appointment->mulai->isToday())
                <form
                    method="POST"
                    action="{{ route('groomer.tasks.start', $appointment) }}"
                >
                    @csrf
                    <button type="submit">Mulai Grooming</button>
                </form>
            @elseif ($appointment->status === 'dikerjakan')
                <form
                    method="POST"
                    action="{{ route('groomer.tasks.finish', $appointment) }}"
                >
                    @csrf

                    <label for="catatan_grooming_{{ $appointment->id }}">
                        Catatan Grooming
                    </label>

                    <textarea
                        id="catatan_grooming_{{ $appointment->id }}"
                        name="catatan_grooming"
                        rows="4"
                        maxlength="2000"
                        required
                    >{{ old('catatan_grooming') }}</textarea>

                    <button type="submit">Selesaikan Grooming</button>
                </form>
            @endif
        </div>

        <hr>
    @empty
        <p>Belum ada tugas grooming.</p>
    @endforelse

    {{ $appointments->links() }}
</body>
</html>