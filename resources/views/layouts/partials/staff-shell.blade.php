
@php
    $user = auth()->user();

    $home = $user && $user->hasRole('admin')
        ? route('admin.dashboard')
        : route('staf.appointments.index');
@endphp

<a href="#konten" class="skip-link">Lewati ke konten</a>

<div class="app-shell">
    <div class="app-frame">

        {{-- SIDEBAR --}}
        <aside class="side">

            {{-- Brand --}}
            <a href="{{ $home }}"
               class="brand"
               aria-label="Pet Care Ops, ke beranda">
                <x-brand-logo size="sidebar" />
            </a>

            {{-- Navigasi --}}
            <nav class="nav" aria-label="Menu utama">

                <p class="nav__title">Operasional</p>

                {{-- DASHBOARD ADMIN --}}
                @role('admin')
                    <x-sidebar-link
                        :href="route('admin.dashboard')"
                        icon="home"
                        label="Dashboard"
                        :active="request()->routeIs('admin.dashboard')"
                    />
                @endrole

                {{-- MENU ADMIN DAN RESEPSIONIS --}}
                @hasanyrole('admin|resepsionis')

                    {{-- Appointment --}}
                    <x-sidebar-link
                        :href="route('staf.appointments.index')"
                        icon="calendar"
                        label="Appointment"
                        :active="request()->routeIs('staf.appointments.*')"
                    />

                    {{-- Kalender Groomer --}}
                    <x-sidebar-link
                        :href="route('staf.groomers.calendar')"
                        icon="calendar"
                        label="Kalender Groomer"
                        :active="request()->routeIs('staf.groomers.calendar')"
                    />

                    {{-- Pengingat Grooming --}}
                    <x-sidebar-link
                        :href="route('staf.pengingat.index')"
                        icon="bell"
                        label="Pengingat Grooming"
                        :active="request()->routeIs('staf.pengingat.*')"
                    />

                    <p class="nav__title">Data</p>

                    {{-- Pemilik --}}
                    <x-sidebar-link
                        :href="route('staf.pages.customers')"
                        icon="users"
                        label="Pemilik"
                        :active="request()->routeIs('staf.pages.customers')
                            || request()->routeIs('staf.pemilik.*')"
                    />

                    {{-- Hewan --}}
                    <x-sidebar-link
                        :href="route('staf.pages.pets')"
                        icon="paw"
                        label="Hewan"
                        :active="request()->routeIs('staf.pages.pets')
                            || request()->routeIs('staf.hewan.*')"
                    />

                @endhasanyrole

                {{-- MENU KHUSUS ADMIN --}}
                @role('admin')

                    <p class="nav__title">Master Data</p>

                    {{-- Layanan --}}
                    <x-sidebar-link
                        :href="route('admin.pages.services')"
                        icon="tag"
                        label="Layanan"
                        :active="request()->routeIs('admin.pages.services')
                            || request()->routeIs('admin.layanan.*')"
                    />

                    {{-- Faktor durasi --}}
                    <x-sidebar-link
                        :href="route('admin.pages.duration-factors')"
                        icon="sliders"
                        label="Faktor durasi"
                        :active="request()->routeIs('admin.pages.duration-factors')
                            || request()->routeIs('admin.faktor-durasi.*')"
                    />

                    {{-- Groomer --}}
                    <x-sidebar-link
                        :href="route('admin.pages.groomers')"
                        icon="scissors"
                        label="Groomer"
                        :active="request()->routeIs('admin.pages.groomers')
                            || request()->routeIs('admin.pages.groomer-shifts')
                            || request()->routeIs('admin.groomer.index')
                            || request()->routeIs('admin.groomer.show')
                            || request()->routeIs('admin.groomer.update')
                            || request()->routeIs('admin.groomer.shift.*')"
                    />

                    {{-- Libur groomer --}}
                    <x-sidebar-link
                        :href="route('admin.pages.time-off')"
                        icon="sun"
                        label="Libur groomer"
                        :active="request()->routeIs('admin.pages.time-off')
                            || request()->routeIs('admin.pages.groomer-time-off')
                            || request()->routeIs('admin.groomer.time-off.*')
                            || request()->routeIs('admin.libur.*')"
                    />

                    <p class="nav__title">Pengaturan</p>

                    {{-- Akun staf --}}
                    <x-sidebar-link
                        :href="route('admin.staff.index')"
                        icon="user"
                        label="Akun staf"
                        :active="request()->routeIs('admin.staff.*')"
                    />

                @endrole

            </nav>

            {{-- FOOTER SIDEBAR --}}
            <div class="side__foot">

                {{-- Profil pengguna --}}
                @include('layouts.partials.user-menu')

                {{-- Logout --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="side__logout-form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="logout"
                        aria-label="Keluar dari akun"
                        title="Keluar"
                    >
                        <x-icon name="logout" />
                        <span>Keluar</span>
                    </button>
                </form>

            </div>

        </aside>

        {{-- KONTEN UTAMA --}}
        <main
            class="app-main"
            id="konten"
            tabindex="-1"
        >
            <div class="app-content">

                {{-- Notifikasi --}}
                @include('layouts.partials.flash')

                {{-- HEADER BERSAMA PET CARE OPS --}}
                @isset($header)
                    <header class="page-header page-header--compact pco-shared-header">

                        <div class="page-header__text">
                            <div class="page-header__title">
                                {{ $header }}
                            </div>
                        </div>

                        {{-- Ornamen paw --}}
                        <span
                            class="pco-header-paw"
                            aria-hidden="true"
                        >🐾</span>

                    </header>
                @endisset

                {{-- Isi halaman --}}
                {{ $slot }}

            </div>
        </main>

    </div>
</div>
