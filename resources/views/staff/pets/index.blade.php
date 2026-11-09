<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Daftar Hewan Pelanggan
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($pets->count())
                    <div class="space-y-4">
                        @foreach ($pets as $pet)
                            <div class="border rounded-lg p-4">
                                <p>
                                    <strong>Nama Hewan:</strong>
                                    {{ $pet->nama }}
                                </p>

                                <p>
                                    <strong>Spesies:</strong>
                                    {{ $pet->spesies }}
                                </p>

                                <p>
                                    <strong>Pemilik:</strong>
                                    {{ $pet->customer->nama }}
                                </p>

                                <a
                                    href="{{ route('staf.pets.grooming-history', $pet) }}"
                                    class="text-blue-600 underline"
                                >
                                    Lihat Riwayat Grooming
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $pets->links() }}
                    </div>
                @else
                    <p>Belum ada data hewan pelanggan.</p>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>