<x-app-layout>
    <x-slot name="title">Tugas Grooming</x-slot>
    <section class="groomer-welcome">
        <div><p class="eyebrow">RUANG GROOMER</p><h2>Hai, {{ auth()->user()->name }}!</h2><p>Berikut daftar appointment yang menjadi tanggung jawabmu.</p></div>
        <img src="{{ asset('images/dog.svg') }}" alt="" aria-hidden="true">
    </section>

    <section class="groomer-task-list">
        @forelse ($appointments as $appointment)
            <article class="card groomer-task-card">
                <div class="groomer-task-card__top"><div><p class="eyebrow">{{ $appointment->mulai->translatedFormat('l, d F Y') }}</p><h2>{{ $appointment->pet?->nama ?? 'Hewan' }}</h2><p>{{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}</p></div><span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($appointment->status) }}">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span></div>
                <div class="groomer-task-meta"><span><x-icon name="clock" /> {{ $appointment->mulai->format('H:i') }}–{{ $appointment->selesai_estimasi->format('H:i') }}</span><span><x-icon name="paw" /> {{ ucfirst($appointment->pet?->spesies ?? 'Hewan') }}</span><span><x-icon name="scissors" /> {{ $appointment->durasi_estimasi }} menit</span></div>
                @if ($appointment->services->isNotEmpty())<div class="groomer-task-services"><strong>Layanan</strong><p>@foreach ($appointment->services as $service) <span>{{ $service->nama_layanan }}</span> @endforeach</p></div>@endif
            </article>
        @empty
            <div class="card empty-state"><img src="{{ asset('images/cat.svg') }}" alt="" aria-hidden="true"><h3>Belum ada tugas grooming</h3><p>Appointment yang ditugaskan kepadamu akan muncul di sini.</p></div>
        @endforelse
    </section>
    <div class="pagination-wrap">{{ $appointments->links() }}</div>
</x-app-layout>
