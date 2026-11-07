<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pengelolaan Akun Staf
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Notifikasi berhasil --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Password sementara setelah reset --}}
            @if (session('temporary_password'))
                <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded">
                    <p class="font-semibold">
                        Password sementara untuk {{ session('reset_user_name') }}:
                    </p>

                    <p class="mt-2 font-mono text-lg">
                        {{ session('temporary_password') }}
                    </p>

                    <p class="mt-2 text-sm">
                        Salin dan berikan password ini kepada staf terkait.
                        Password hanya ditampilkan pada kesempatan ini.
                    </p>
                </div>
            @endif

            {{-- Notifikasi error --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tombol tambah akun --}}
            <div class="mb-4">
                <a href="{{ route('admin.staff.create') }}"
                   class="inline-block px-4 py-2 bg-blue-600 text-white rounded">
                    + Tambah Akun Staf
                </a>
            </div>

            {{-- Tabel akun staf --}}
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4">No.</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t">
                                <td class="p-4">
                                    {{ $users->firstItem() + $loop->index }}
                                </td>

                                <td class="p-4">{{ $user->name }}</td>

                                <td class="p-4">{{ $user->email }}</td>

                                <td class="p-4">
                                    {{ $user->roles->pluck('name')->join(', ') }}
                                </td>

                                <td class="p-4">
                                    @if ($user->is_active)
                                        <span class="text-green-600">Aktif</span>
                                    @else
                                        <span class="text-red-600">Nonaktif</span>
                                    @endif
                                </td>

                                <td class="p-4">
                                    @if ($user->is_active && $user->id !== auth()->id())

                                        {{-- Nonaktifkan akun --}}
                                        <form method="POST"
                                              action="{{ route('admin.staff.deactivate', $user) }}"
                                              onsubmit="return confirm('Yakin ingin menonaktifkan akun ini?')">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="text-red-600 underline">
                                                Nonaktifkan
                                            </button>
                                        </form>

                                        {{-- Reset password --}}
                                        <form method="POST"
                                              action="{{ route('admin.staff.reset-password', $user) }}"
                                              onsubmit="return confirm('Yakin ingin mereset password akun ini?')"
                                              class="mt-2">
                                            @csrf
                                            @method('PUT')

                                            <button type="submit"
                                                    class="text-blue-600 underline">
                                                Reset Password
                                            </button>
                                        </form>

                                    @elseif ($user->id === auth()->id())
                                        <span class="text-gray-500">Akun sendiri</span>
                                    @else
                                        <form method="POST"
                                            action="{{ route('admin.staff.activate', $user) }}"
                                            onsubmit="return confirm('Yakin ingin mengaktifkan akun ini?')">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="text-green-600 underline">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">
                                    Belum ada akun staf.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $users->links() }}
            </div>

        </div>
    </div>
</x-app-layout>