<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kalender Beban Kerja Groomer
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET"
                    action="{{ route('staf.groomers.calendar') }}"
                    class="flex flex-wrap items-end gap-4">

                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium">
                            Tanggal Mulai
                        </label>
                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            value="{{ $tanggalMulai }}"
                            class="mt-1 rounded-md border-gray-300"
                        >
                    </div>

                    <div>
                        <label for="tanggal_akhir" class="block text-sm font-medium">
                            Tanggal Akhir
                        </label>
                        <input
                            type="date"
                            id="tanggal_akhir"
                            name="tanggal_akhir"
                            value="{{ $tanggalAkhir }}"
                            class="mt-1 rounded-md border-gray-300"
                        >
                    </div>

                    <div>
                        <label for="groomer_id" class="block text-sm font-medium">
                            Groomer
                        </label>
                        <select
                            id="groomer_id"
                            name="groomer_id"
                            class="mt-1 rounded-md border-gray-300"
                        >
                            <option value="">Semua Groomer</option>

                            @foreach ($groomers as $groomer)
                                <option
                                    value="{{ $groomer->id }}"
                                    @selected((string) $groomerId === (string) $groomer->id)
                                >
                                    {{ $groomer->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('staf.groomers.calendar') }}"
                        class="px-4 py-2 border rounded-md"
                    >
                        Reset
                    </a>
                </form>
            </div>

            @forelse ($appointmentsByDate as $tanggal => $items)
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-lg mb-4">
                        {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
                    </h3>

                    <div class="space-y-4">
                        @foreach ($items as $appointment)
                            <div class="border rounded-md p-4">
                                <div class="flex flex-wrap justify-between gap-2">
                                    <div>
                                        <p class="font-semibold">
                                            {{ $appointment->mulai->format('H:i') }}
                                            -
                                            {{ $appointment->selesai_estimasi->format('H:i') }}
                                        </p>

                                        <p>
                                            Groomer:
                                            {{ $appointment->groomer?->user?->name ?? '-' }}
                                        </p>

                                        <p>
                                            Hewan:
                                            {{ $appointment->pet?->nama ?? '-' }}
                                        </p>

                                        <p>
                                            Pemilik:
                                            {{ $appointment->pet?->customer?->nama ?? '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <span class="px-3 py-1 rounded bg-gray-100 text-sm">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    Belum ada appointment pada rentang tanggal tersebut.
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>