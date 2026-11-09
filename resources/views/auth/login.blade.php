<x-guest-layout>
    <p class="auth-eyebrow">Selamat datang kembali</p>
    <h1 class="auth-title" id="login-title"><span>Login</span></h1>
    <p class="auth-lead">Masuk untuk mengelola reservasi dan jadwal grooming hari ini.</p>

    @if (session('status'))
        <p class="auth-status" role="status">{{ session('status') }}</p>
    @endif

    <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="auth-field @error('email') has-error @enderror">
            <label class="auth-label" for="email">Email</label>
            <div class="auth-control">
                <svg class="auth-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                </svg>
                <input class="auth-input" id="email" name="email" type="email"
                       value="{{ old('email') }}" autocomplete="username"
                       placeholder="admin@petcare.id" required autofocus
                       aria-describedby="email-error"
                       @error('email') aria-invalid="true" @enderror>
            </div>
            <p class="auth-error" id="email-error" role="alert"
               data-msg="Isi email Anda, misalnya admin@petcare.id.">{{ $errors->first('email') ?: 'Isi email Anda, misalnya admin@petcare.id.' }}</p>
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Kata sandi</label>
            <div class="auth-control auth-control--pw">
                <svg class="auth-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>
                </svg>
                <input class="auth-input" id="password" name="password" type="password"
                       autocomplete="current-password" placeholder="Masukkan kata sandi"
                       required aria-describedby="password-error">
                <button type="button" class="auth-toggle" id="password-toggle"
                        aria-pressed="false" aria-controls="password">Lihat</button>
            </div>
            <p class="auth-error" id="password-error" role="alert"
               data-msg="Isi kata sandi Anda.">Isi kata sandi Anda.</p>
        </div>

        <div class="auth-row">
            <label class="auth-check" for="remember">
                <input id="remember" type="checkbox" name="remember" value="1"
                       {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat saya</span>
            </label>
        </div>

        <button class="btn btn--block" id="login-submit" type="submit">Masuk</button>
        <p class="auth-help">Belum memiliki akun? Hubungi admin salon.</p>
    </form>
</x-guest-layout>
