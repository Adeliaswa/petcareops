
@extends('layouts.public')

@section('title', 'Pet Care Ops | Pet Grooming & Spa')
@section('description', 'Perawatan grooming anjing dan kucing dengan jadwal yang mudah dan estimasi durasi yang sesuai kebutuhan hewan.')

@section('content')

<header class="public-header">
    <a href="{{ route('home') }}" class="public-brand">
        <img src="{{ asset('images/petcare-logo.svg') }}" alt="Pet Care Ops">
        <span>
            <strong>Pet Care Ops</strong>
            <small>Pet Grooming &amp; Spa</small>
        </span>
    </a>

    <nav class="public-nav" aria-label="Navigasi utama">
        <a href="#layanan">Layanan</a>
        <a href="#cara-kerja">Cara Kerja</a>
        <a href="{{ route('login') }}">Masuk Staf</a>
        <a class="public-button public-button--small"
           href="{{ route('booking.create') }}">
            Booking Sekarang
        </a>
    </nav>
</header>

<main id="main-content">

    {{-- HERO --}}
    <section class="public-hero">

        <div class="public-hero__copy">
            <span class="public-eyebrow">
                <span class="public-eyebrow__dot"></span>
                PET GROOMING &amp; SPA
            </span>

            <h1>
                Perawatan penuh kasih untuk
                <em>sahabat berbulu.</em>
            </h1>

            <p class="public-hero__lead">
                Setiap anjing dan kucing punya kebutuhan berbeda.
                Temukan layanan grooming yang tepat dan atur jadwal
                perawatan si kecil dengan lebih mudah.
            </p>

            <div class="public-hero__actions">
                <a href="{{ route('booking.create') }}" class="public-button">
                    Mulai Booking <span>→</span>
                </a>

                <a href="#layanan" class="public-text-link">
                    Jelajahi layanan ↓
                </a>
            </div>

            <div class="public-hero__trust">
                <span class="public-trust-icon">♡</span>
                <span>
                    <strong>Care made personal</strong>
                    <small>Estimasi sesuai kebutuhan setiap hewan</small>
                </span>
            </div>
        </div>

        <div class="public-hero__visual">

            <div class="public-hero__blob"></div>

            <div class="public-hero__image public-hero__image--dog">
                <img
                    src="{{ asset('images/anjing.webp') }}"
                    alt="Anjing bersama kucing"
                    fetchpriority="high"
                >
                <span class="public-image-label">For happy dogs</span>
            </div>

            <div class="public-hero__image public-hero__image--cat">
                <img
                    src="{{ asset('images/kucing.webp') }}"
                    alt="Kucing kesayangan"
                    fetchpriority="high"
                >
                <span class="public-image-label">For lovely cats</span>
            </div>

            <span class="public-hero__spark public-hero__spark--one">✳</span>
            <span class="public-hero__spark public-hero__spark--two">✦</span>

            <div class="public-hero__note">
                <span class="public-note-icon">✦</span>
                <div>
                    <strong>Every pet is special</strong>
                    <small>Nyaman, terencana, dan penuh perhatian</small>
                </div>
            </div>

        </div>
    </section>


    {{-- DOG & CAT SHOWCASE --}}
    <section class="public-pet-showcase" id="pet-showcase">

        <div class="public-section-heading public-pet-heading">
            <span class="public-eyebrow">OUR LOVELY FRIENDS</span>
            <h2>
                Perawatan spesial untuk
                <em>setiap sahabat berbulu.</em>
            </h2>
            <p>
                Anjing maupun kucing, semuanya berhak mendapatkan
                perawatan yang nyaman dan penuh perhatian.
            </p>
        </div>

        <div class="public-pet-grid">

            {{-- DOG CARD --}}
            <article class="public-pet-card public-pet-card--dog">

                <div class="public-pet-card__top">
                    <span>DOG CARE</span>
                    <span class="public-pet-card__plus">+250</span>
                </div>

                <div class="public-pet-card__image">
                    <img
                        src="{{ asset('images/dogo.webp') }}"
                        alt="Anjing lucu yang siap mendapatkan grooming"
                        loading="lazy"
                    >
                    <span class="public-pet-card__floating">
                        🐾 Happy dog
                    </span>
                </div>

                <div class="public-pet-card__meta">
                    <span class="public-pet-tag">PET A DOG</span>
                    <span class="public-pet-stars">★★★★★</span>
                </div>

                <div class="public-pet-card__description">
                    <strong>Power:</strong>
                    Bantu si doggo tampil rapi, wangi, dan nyaman
                    dengan grooming yang lembut dan terjadwal.
                </div>

                <div class="public-pet-card__footer">
                    BEST FOR BATH · TRIM · DRY
                </div>

                <span class="public-pet-decoration">🐾</span>

            </article>


            {{-- CAT CARD --}}
            <article class="public-pet-card public-pet-card--cat">

                <div class="public-pet-card__top">
                    <span>CAT CARE</span>
                    <span class="public-pet-card__plus">+250</span>
                </div>

                <div class="public-pet-card__image">
                    <img
                        src="{{ asset('images/meow.webp') }}"
                        alt="Kucing lucu yang siap mendapatkan grooming"
                        loading="lazy"
                    >
                    <span class="public-pet-card__floating">
                        🐱 Lovely cat
                    </span>
                </div>

                <div class="public-pet-card__meta">
                    <span class="public-pet-tag">PET A CAT</span>
                    <span class="public-pet-stars">★★★★★</span>
                </div>

                <div class="public-pet-card__description">
                    <strong>Power:</strong>
                    Bantu si meong tetap bersih, halus, dan rileks
                    dengan proses grooming yang penuh perhatian.
                </div>

                <div class="public-pet-card__footer">
                    BEST FOR BRUSH · CLEAN · CALM
                </div>

                <span class="public-pet-decoration">🐾</span>

            </article>

        </div>

        <div class="public-pet-bottom">
            <span>01</span>
            <strong>Khusus anjing &amp; kucing</strong>
            <i></i>
            <span>02</span>
            <strong>Nyaman dan penuh perhatian</strong>
            <i></i>
            <span>03</span>
            <strong>Siap untuk booking grooming</strong>
        </div>

    </section>


    {{-- HOW IT WORKS --}}
    <section class="public-section public-how" id="cara-kerja">

        <div class="public-section-heading">
            <span class="public-eyebrow">HOW IT WORKS</span>

            <h2>
                Langkah booking yang
                <em>sederhana.</em>
            </h2>

            <p>
                Dari data hewan sampai jadwal grooming, semuanya dibuat
                lebih rapi supaya pelanggan lebih mudah memahami prosesnya.
            </p>
        </div>

        <div class="public-steps">

            <article class="public-step-card">
                <div class="public-step-top">
                    <span class="public-step-number">01</span>
                    <span class="public-step-symbol">🐶</span>
                </div>

                <h3>Isi data pelanggan</h3>

                <p>
                    Masukkan data pelanggan dan pilih hewan
                    yang akan mendapatkan layanan.
                </p>

                <span class="public-step-foot">PELANGGAN &amp; HEWAN</span>
            </article>

            <article class="public-step-card public-step-card--featured">
                <div class="public-step-top">
                    <span class="public-step-number">02</span>
                    <span class="public-step-symbol">🐾</span>
                </div>

                <h3>Hitung estimasi</h3>

                <p>
                    Sistem menghitung perkiraan durasi berdasarkan
                    ukuran, bulu, perilaku, dan layanan.
                </p>

                <span class="public-step-foot">ESTIMASI DURASI</span>
            </article>

            <article class="public-step-card">
                <div class="public-step-top">
                    <span class="public-step-number">03</span>
                    <span class="public-step-symbol">🦴</span>
                </div>

                <h3>Pilih jadwal</h3>

                <p>
                    Pilih slot yang tersedia lalu lanjutkan
                    ke konfirmasi booking grooming.
                </p>

                <span class="public-step-foot">PILIH SLOT</span>
            </article>

        </div>
    </section>


    {{-- SERVICES --}}
    <section class="public-section public-services-section" id="layanan">

        <div class="public-section-heading public-section-heading--row">

            <div>
                <span class="public-eyebrow">A LITTLE EXTRA CARE</span>

                <h2>
                    Temukan perawatan
                    <em>untuk sahabatmu.</em>
                </h2>

                <p>
                    Pilih layanan sesuai kebutuhan anjing maupun kucing
                    kesayanganmu.
                </p>
            </div>

            <a href="{{ route('booking.create') }}" class="public-text-link">
                Lihat semua layanan →
            </a>

        </div>

        @if($services->isNotEmpty())

            <div class="public-service-grid">

                @foreach($services as $service)

                    <article class="public-service-card">

                        <div class="public-service-card__top">
                            <span class="public-service-icon">🐾</span>
                            <span class="public-service-tag">PET CARE</span>
                        </div>

                        <h3>{{ $service->nama }}</h3>

                        <p>
                            {{ $service->deskripsi ?: 'Perawatan pilihan untuk menjaga kebersihan dan kenyamanan hewan kesayangan.' }}
                        </p>

                        <div class="public-service-card__meta">

                            <span>
                                <small>Durasi dasar</small>
                                <strong>{{ $service->durasi_dasar }} menit</strong>
                            </span>

                            <span>
                                <small>Harga mulai</small>
                                <strong>
                                    Rp{{ number_format((float) $service->harga, 0, ',', '.') }}
                                </strong>
                            </span>

                        </div>

                        <a href="{{ route('booking.create') }}"
                           class="public-service-link">
                            Pilih layanan →
                        </a>

                    </article>

                @endforeach

            </div>

        @else

            <div class="public-empty">
                <span>🐾</span>
                <div>
                    <strong>Layanan sedang disiapkan</strong>
                    <p>
                        Silakan kembali beberapa saat lagi untuk
                        melihat pilihan perawatan.
                    </p>
                </div>
            </div>

        @endif

    </section>


    {{-- CTA --}}
    <section class="public-cta">

        <span class="public-cta__paw public-cta__paw--one">🐾</span>
        <span class="public-cta__paw public-cta__paw--two">🦴</span>

        <div class="public-cta__content">
            <span class="public-eyebrow">LET'S MAKE THEM FEEL GOOD</span>

            <h2>
                Siap manjakan
                <em>sahabat berbulu?</em>
            </h2>

            <p>
                Atur jadwal grooming dengan lebih mudah
                bersama Pet Care Ops.
            </p>
        </div>

        <a href="{{ route('booking.create') }}"
           class="public-button public-button--light">
            Mulai Booking →
        </a>

    </section>

</main>


{{-- FOOTER --}}
<footer class="public-footer">

    <a href="{{ route('home') }}" class="public-brand">
        <img src="{{ asset('images/petcare-logo.svg') }}" alt="Pet Care Ops">

        <span>
            <strong>Pet Care Ops</strong>
            <small>Pet Grooming &amp; Spa</small>
        </span>
    </a>

    <span>
        © {{ date('Y') }} Pet Care Ops. Made with care for every pet.
    </span>

</footer>

@endsection
