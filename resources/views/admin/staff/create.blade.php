<x-app-layout>
    <x-slot name="title">Tambah Akun Staf</x-slot>

    <header class="page-intro-row">
        <div><p class="eyebrow">PENGATURAN TIM</p><h2 class="content-title">Tambah akun staf</h2><p class="content-lead">Buat akses untuk anggota tim yang akan menggunakan Pet Care Ops.</p></div>
        <a href="{{ route('admin.staff.index') }}" class="btn btn--soft">Kembali ke daftar</a>
    </header>

    <section class="card account-form-card">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="profile-form" novalidate>
            @csrf
            <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                <label class="field-label" for="name">Nama lengkap <span class="req">*</span></label>
                <input class="form-input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" autocomplete="off" required>
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                <label class="field-label" for="email">Alamat email <span class="req">*</span></label>
                <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@petcare.id" autocomplete="off" required>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field {{ $errors->has('role') ? 'has-error' : '' }}">
                <label class="field-label" for="role">Peran staf <span class="req">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Pilih peran</option>
                    <option value="resepsionis" @selected(old('role') === 'resepsionis')>Resepsionis</option>
                    <option value="groomer" @selected(old('role') === 'groomer')>Groomer</option>
                </select>
                <p class="field-hint">Profil groomer akan dibuat otomatis ketika peran Groomer dipilih.</p>
                @error('role')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-row">
                <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                    <label class="field-label" for="password">Password awal <span class="req">*</span></label>
                    <input class="form-input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <p class="field-hint">Minimal 8 karakter.</p>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    <label class="field-label" for="password_confirmation">Konfirmasi password <span class="req">*</span></label>
                    <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    @error('password_confirmation')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions"><button type="submit" class="btn">Simpan akun staf</button><a href="{{ route('admin.staff.index') }}" class="btn btn--soft">Batal</a></div>
        </form>
    </section>
</x-app-layout>
