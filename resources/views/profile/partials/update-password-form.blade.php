<form method="post" action="{{ route('password.update') }}" class="profile-form">
    @csrf
    @method('put')

    <div class="field {{ $errors->updatePassword->has('current_password') ? 'has-error' : '' }}">
        <label class="field-label" for="update_password_current_password">Password saat ini</label>
        <input class="form-input" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" required>
        @foreach ($errors->updatePassword->get('current_password') as $message)<p class="field-error">{{ $message }}</p>@endforeach
    </div>

    <div class="field {{ $errors->updatePassword->has('password') ? 'has-error' : '' }}">
        <label class="field-label" for="update_password_password">Password baru</label>
        <input class="form-input" id="update_password_password" name="password" type="password" autocomplete="new-password" required>
        @foreach ($errors->updatePassword->get('password') as $message)<p class="field-error">{{ $message }}</p>@endforeach
        <p class="field-hint">Minimal 8 karakter.</p>
    </div>

    <div class="field {{ $errors->updatePassword->has('password_confirmation') ? 'has-error' : '' }}">
        <label class="field-label" for="update_password_password_confirmation">Konfirmasi password baru</label>
        <input class="form-input" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        @foreach ($errors->updatePassword->get('password_confirmation') as $message)<p class="field-error">{{ $message }}</p>@endforeach
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Perbarui password</button>
        @if (session('status') === 'password-updated')
            <span class="form-success"><x-icon name="check-circle" /> Password diperbarui.</span>
        @endif
    </div>
</form>
