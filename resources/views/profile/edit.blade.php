<x-app-layout>
    <x-slot name="title">Profil Saya</x-slot>

    <section class="profile-intro">
        <div class="profile-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        <div class="profile-intro__text">
            <p class="eyebrow">PENGATURAN AKUN</p>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
        </div>
        <x-role-badge :role="$user->getRoleNames()->first() ?? 'staf'" />
    </section>

    <div class="profile-grid">
        <section class="card profile-card">
            <div class="profile-card__heading">
                <span class="profile-card__icon"><x-icon name="user" /></span>
                <div><h2>Informasi profil</h2><p>Perbarui nama dan alamat email akunmu.</p></div>
            </div>
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="card profile-card">
            <div class="profile-card__heading">
                <span class="profile-card__icon profile-card__icon--pink"><x-icon name="check-circle" /></span>
                <div><h2>Keamanan akun</h2><p>Gunakan password yang kuat dan tidak mudah ditebak.</p></div>
            </div>
            @include('profile.partials.update-password-form')
        </section>
    </div>
</x-app-layout>
