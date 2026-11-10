@if (session('success') || session('status') || session('error'))
    @php
        $message = session('success') ?? session('error') ?? (session('status') === 'profile-updated' ? 'Profil berhasil diperbarui.' : (session('status') === 'password-updated' ? 'Password berhasil diperbarui.' : session('status')));
        $kind = session('error') ? 'error' : 'success';
    @endphp
    <div class="flash-message flash-message--{{ $kind }} flash-message--banner" role="status" x-data="flashMessage(6000)" x-show="show" x-transition>
        <span class="flash-message__pet" aria-hidden="true"></span>
        <div class="flash-message__content">
            <strong class="flash-message__title">{{ $kind === 'success' ? 'Berhasil!' : 'Perlu diperhatikan' }}</strong>
            <span>{{ $message }}</span>
        </div>
        <button type="button" class="flash-message__close" @click="dismiss()" aria-label="Tutup notifikasi">×</button>
    </div>
@endif

@if ($errors->any() && !request()->routeIs('profile.edit'))
    <div class="form-alert" role="alert">
        <strong>Periksa kembali isianmu.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
