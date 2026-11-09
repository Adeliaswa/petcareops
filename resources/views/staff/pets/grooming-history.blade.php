
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Grooming Hewan
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold">
                    {{ $pet->nama }}
                </h3>

                <p class="text-sm text-gray-600">
                    Pemilik: {{ $pet->customer->nama }}
                </p>

                <p class="text-sm text-gray-600">
                    {{ ucfirst($pet->spesies) }} - {{ $pet->ras }}
                </p>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold mb-4">
                    Riwayat Grooming
                </h3>

                @forelse ($groomingRecords as $appointment)
                    <div class="border-b py-4">
                        <p>
                            Tanggal:
                            {{ $appointment->mulai->format('d-m-Y H:i') }}
                        </p>

                        <p>
                            Groomer:
                            {{ $appointment->groomer->user->name }}
                        </p>

                        <p>
                            Layanan:
                            {{ $appointment->services->pluck('nama')->join(', ') }}
                        </p>

                        <p>
                            Durasi aktual:
                            {{ $appointment->groomingRecord->durasi_aktual }}
                            menit
                        </p>

                        <p>
                            Catatan:
                            {{ $appointment->groomingRecord->catatan_grooming }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-600">
                        Belum ada riwayat grooming untuk hewan ini.
                    </p>
                @endforelse

                <div class="mt-4">
                    {{ $groomingRecords->links() }}
                </div>
            </div>

            <a href="{{ route('staf.pages.pets') }}"
               class="text-blue-600 hover:underline">
                Kembali ke daftar hewan
            </a>

        </div>
    </div>
</x-app-layout>
