<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Daftar Pengingat Grooming
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

            @if (session('success'))
    <div class="mb-4 text-green-700">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('staf.pengingat.jalankan') }}">
    @csrf
    <button type="submit">
        Jalankan Pengingat
    </button>
</form>

                <form method="GET" action="{{ route('staf.pengingat.index') }}">
                    <label for="status">Filter Status</label>

                    <select name="status" id="status">
                        <option value="">Semua Status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>
                            Menunggu
                        </option>
                        <option value="terkirim" @selected(request('status') === 'terkirim')>
                            Terkirim
                        </option>
                        <option value="ditutup" @selected(request('status') === 'ditutup')>
                            Ditutup
                        </option>
                    </select>

                    <button type="submit">Filter</button>
                </form>

                <div class="mt-6">
                    @forelse ($reminders as $reminder)
                        <div class="border-b py-4">
                            <p>Hewan: {{ $reminder->pet->nama }}</p>
                            <p>Pemilik: {{ $reminder->pet->customer->nama }}</p>
                            <p>
                                Jatuh tempo:
                                {{ $reminder->tanggal_jatuh_tempo->format('d-m-Y') }}
                            </p>
                            <p>Status: {{ $reminder->status }}</p>

                            @if ($reminder->wa_link)
    <a
        href="{{ $reminder->wa_link }}"
        target="_blank"
        rel="noopener noreferrer"
    >
        Kirim WhatsApp Manual
    </a>
@endif
                            <p>
                                Terlambat:
                                {{ max(0, (int) now()->startOfDay()->diffInDays(
                                    $reminder->tanggal_jatuh_tempo,
                                    false
                                ) * -1) }} hari
                            </p>
                        </div>
                    @empty
                        <p>Belum ada data pengingat grooming.</p>
                    @endforelse
                </div>

                {{ $reminders->links() }}

            </div>
        </div>
    </div>
</x-app-layout>