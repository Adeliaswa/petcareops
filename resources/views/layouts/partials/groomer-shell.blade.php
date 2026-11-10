
@php
    $user = auth()->user();
@endphp

<a href="#konten" class="skip-link">Lewati ke konten</a>

<div class="g-app">

    {{-- Header --}}
    <header class="g-top">
        <a href="{{ route('groomer.tasks.index') }}"
           class="brand"
           aria-label="Pet Care Ops, ke tugas hari ini">
            <x-brand-logo size="groomer" />
        </a>

        <div class="g-top__who">
            <span class="g-top__name">
                {{ $user->name }}
            </span>

            <x-role-badge role="groomer" class="badge--sm" />
        </div>
    </header>

    {{-- Konten utama --}}
    <main class="g-main" id="konten" tabindex="-1">

        @include('layouts.partials.flash')

        @isset($header)
            <header class="page-header page-header--compact">
                <div class="page-header__text">
                    <div class="page-header__title">
                        {{ $header }}
                    </div>
                </div>
            </header>
        @endisset

        {{ $slot }}

    </main>

    {{-- Navigasi bawah --}}
    <nav class="g-nav" aria-label="Menu groomer">

        {{-- Tugas --}}
        <a href="{{ route('groomer.tasks.index') }}"
           class="g-nav__link {{ request()->routeIs('groomer.tasks.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('groomer.tasks.*'))
               aria-current="page"
           @endif>

            <span class="g-nav__pill">
                <x-icon name="clipboard" />
            </span>

            <span>Tugas</span>
        </a>

        {{-- Profil --}}
        <a href="{{ route('profile.edit') }}"
           class="g-nav__link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('profile.*'))
               aria-current="page"
           @endif>

            <span class="g-nav__pill">
                <x-icon name="user" />
            </span>

            <span>Profil</span>
        </a>

        {{-- Logout --}}
        <form method="POST"
              action="{{ route('logout') }}"
              class="g-nav__logout-form">

            @csrf

            <button type="submit"
                    class="g-nav__link g-nav__logout"
                    aria-label="Keluar dari akun"
                    title="Keluar">

                <span class="g-nav__pill">
                    <x-icon name="logout" />
                </span>

                <span>Keluar</span>

            </button>

        </form>

    </nav>

</div>
