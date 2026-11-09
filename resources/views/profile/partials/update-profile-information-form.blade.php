<form method="post" action="{{ route('profile.update') }}" class="profile-form">
    @csrf
    @method('patch')

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label class="field-label" for="name">Nama lengkap</label>
        <input class="form-input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
        <label class="field-label" for="email">Alamat email</label>
        <input class="form-input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="field-hint">Alamat email belum diverifikasi.</p>
        @endif
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Simpan perubahan</button>
        @if (session('status') === 'profile-updated')
            <span class="form-success"><x-icon name="check-circle" /> Tersimpan.</span>
        @endif
    </div>
</form>
