<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Akun Staf
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.staff.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block mb-2">Nama Lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full border-gray-300 rounded-md"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block mb-2">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full border-gray-300 rounded-md"
                        >
                    </div>

                    <div class="mb-4">
                        <label for="role" class="block mb-2">Role</label>
                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full border-gray-300 rounded-md"
                        >
                            <option value="">Pilih Role</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                            <option value="resepsionis" @selected(old('role') === 'resepsionis')>Resepsionis</option>
                            <option value="groomer" @selected(old('role') === 'groomer')>Groomer</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block mb-2">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            class="w-full border-gray-300 rounded-md"
                        >
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block mb-2">
                            Konfirmasi Password
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            minlength="8"
                            class="w-full border-gray-300 rounded-md"
                        >
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded"
                        >
                            Simpan Akun
                        </button>

                        <a
                            href="{{ route('admin.staff.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded"
                        >
                            Kembali
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>