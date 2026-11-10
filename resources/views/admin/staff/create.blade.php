<x-app-layout>
    <x-slot name="title">Tambah Akun Staf</x-slot>


    <header class="page-intro-row">
        <div>
            <p class="eyebrow">PENGATURAN TIM</p>
            <h2 class="content-title">Tambah Akun Staf</h2>
            <p class="content-lead">
                Buat akun baru untuk anggota tim Pet Care Ops dan tentukan
                hak akses sesuai perannya.
            </p>
        </div>
    </header>


    <section class="card account-list-card">


        <div class="section-heading">
            <div>
                <h2 class="section-heading__title">Informasi Akun</h2>
                <p class="section-heading__desc">
                    Lengkapi seluruh informasi berikut untuk membuat akun staf.
                </p>
            </div>
        </div>


        {{-- Pesan kesalahan umum --}}
        @if ($errors->has('account'))
            <div class="form-alert" role="alert">
                {{ $errors->first('account') }}
            </div>
        @endif


        {{-- Form Tambah Akun --}}
        <form
            method="POST"
            action="{{ route('admin.staff.store') }}"
        >
            @csrf


            {{-- Nama --}}
            <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name" class="field-label">
                    Nama Lengkap <span class="req">*</span>
                </label>


                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap staf"
                    maxlength="255"
                    autocomplete="name"
                    required
                    autofocus
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                >


                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Email --}}
            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email" class="field-label">
                    Alamat Email <span class="req">*</span>
                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    placeholder="contoh@email.com"
                    maxlength="255"
                    autocomplete="email"
                    required
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                >


                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Role --}}
            <div class="field {{ $errors->has('role') ? 'has-error' : '' }}">
                <label for="role" class="field-label">
                    Peran Akun <span class="req">*</span>
                </label>


                <div class="control">
                    <select
                        id="role"
                        name="role"
                        class="form-select"
                        required
                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                    >
                        <option value="">Pilih peran staf</option>


                        <option
                            value="admin"
                            @selected(old('role') === 'admin')
                        >
                            Admin
                        </option>


                        <option
                            value="resepsionis"
                            @selected(old('role') === 'resepsionis')
                        >
                            Resepsionis
                        </option>


                        <option
                            value="groomer"
                            @selected(old('role') === 'groomer')
                        >
                            Groomer
                        </option>
                    </select>
                </div>


                @error('role')
                    <p class="field-error">{{ $message }}</p>
                @enderror


                <p class="field-hint">
                    Pilih peran berdasarkan tanggung jawab anggota tim.
                </p>
            </div>


            {{-- Password --}}
            <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password" class="field-label">
                    Password <span class="req">*</span>
                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="Masukkan password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                >


                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror


                <p class="field-hint">
                    Password harus memiliki minimal 8 karakter.
                </p>
            </div>


            {{-- Konfirmasi Password --}}
            <div class="field {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                <label for="password_confirmation" class="field-label">
                    Konfirmasi Password <span class="req">*</span>
                </label>


                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Masukkan kembali password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >


                @error('password_confirmation')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- Informasi --}}
            <div class="field-hint" style="margin-top: 1rem;">
                <strong>Informasi:</strong>
                Akun yang berhasil dibuat akan langsung memiliki status aktif
                dan dapat digunakan untuk masuk ke sistem sesuai hak aksesnya.
            </div>


            {{-- Tombol --}}
            <div class="form-actions">
                <button type="submit" class="btn">
                    <x-icon name="plus" />
                    Simpan Akun
                </button>


                <a
                    href="{{ route('admin.staff.index') }}"
                    class="btn btn--soft"
                >
                    Batal
                </a>
            </div>


        </form>
    </section>
</x-app-layout>