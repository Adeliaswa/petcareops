
@extends('layouts.public')

@section('title', 'Booking Grooming | Pet Care Ops')
@section('description', 'Pesan layanan grooming anjing dan kucing dengan mudah bersama Pet Care Ops.')

@section('content')

<div class="public-booking-shell">

    {{-- HEADER --}}
    <header class="public-header public-header--compact">
        <a href="{{ route('home') }}" class="public-brand">
            <img src="{{ asset('images/petcare-logo.svg') }}" alt="Pet Care Ops">
            <span>
                <strong>Pet Care Ops</strong>
                <small>Pet Grooming &amp; Spa</small>
            </span>
        </a>

        <a class="public-text-link" href="{{ route('home') }}">
            ← Kembali ke beranda
        </a>
    </header>

    {{-- INTRO --}}
    <div class="public-booking-heading">
        <span class="public-eyebrow">ONLINE BOOKING</span>
        <h1>Booking Grooming</h1>
        <p>
            Isi informasi dirimu dan hewan kesayangan,
            pilih layanan, kemudian tentukan jadwal grooming.
        </p>
    </div>

    @php
        $bookingConfig = [
            'mode' => 'public',

            'services' => $services->map(fn ($service) => [
                'id' => $service->id,
                'nama' => $service->nama,
                'harga' => $service->harga,
                'durasi' => $service->durasi_dasar,
            ])->values(),

            'factors' => $factors->map(fn ($factor) => [
                'kategori' => $factor->kategori,
                'key' => $factor->key,
                'label' => $factor->label,
            ])->values(),

            'urls' => [
                'pelanggan' => route('booking.pelanggan'),
                'estimasi' => route('booking.estimasi'),
                'slot' => route('booking.slot'),
                'konfirmasi' => route('booking.konfirmasi'),
            ],
        ];
    @endphp

    <section
        class="booking-page"
        x-data="staffBooking"
    >

        <script type="application/json" data-booking-config>
            {!! json_encode(
                $bookingConfig,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
            ) !!}
        </script>

        {{-- BOOKING INTRO --}}
        <div class="booking-intro">
            <div>
                <span class="booking-eyebrow">BOOKING ONLINE</span>

                <h1 class="booking-title">
                    Jadwalkan Grooming
                </h1>

                <p class="booking-description">
                    Berikan perawatan terbaik untuk sahabat berbulu
                    dengan jadwal yang sesuai kebutuhannya.
                </p>
            </div>

            <a
                href="{{ route('home') }}"
                class="booking-back-button"
            >
                Kembali ke Beranda
            </a>
        </div>

        <div class="booking-layout">

            <div class="booking-main">

                {{-- ========================================= --}}
                {{-- 01. DATA PELANGGAN --}}
                {{-- ========================================= --}}

                <section class="booking-card">

                    <div class="booking-section-heading">
                        <span class="booking-step">01</span>

                        <div>
                            <h2>Data Pelanggan</h2>
                            <p>
                                Masukkan nomor WhatsApp untuk memeriksa
                                apakah kamu sudah terdaftar.
                            </p>
                        </div>
                    </div>

                    {{-- PENCARIAN PELANGGAN --}}
                    <div class="booking-field booking-field-full">

                        <label for="booking-search">
                            Nomor WhatsApp *
                        </label>

                        <input
                            id="booking-search"
                            type="tel"
                            inputmode="tel"
                            placeholder="Contoh: 081234567890"
                            x-model="customerQuery"
                            x-on:input="handleCustomerInput($event.target.value)"
                            x-on:input.debounce.500ms="searchCustomers()"
                            autocomplete="tel"
                            :disabled="!!selectedCustomer"
                        >

                        <p class="booking-hint">
                            Gunakan nomor WhatsApp aktif agar kami
                            dapat menghubungimu terkait booking.
                        </p>

                        <p
                            class="booking-hint"
                            x-show="customerLookupLoading"
                            x-cloak
                        >
                            Sedang memeriksa nomor WhatsApp...
                        </p>

                        <p
                            class="booking-hint"
                            x-show="searchMessage"
                            x-text="searchMessage"
                            x-cloak
                        ></p>

                    </div>

                    {{-- HASIL PENCARIAN --}}
                    <div
                        class="booking-search-results"
                        x-show="customerResults.length > 0 && !selectedCustomer"
                        x-cloak
                    >
                        <template
                            x-for="customer in customerResults"
                            :key="customer.id"
                        >
                            <button
                                type="button"
                                class="booking-search-result"
                                @click="selectCustomer(customer)"
                            >
                                <span>
                                    <strong x-text="isPublicBooking ? 'Nomor WhatsApp terdaftar' : customer.nama"></strong>
                                    <small x-text="isPublicBooking ? ((customer.pets?.length || 0) + ' hewan terdaftar') : customer.no_wa"></small>
                                </span>

                                <span class="booking-select-label">
                                    Pilih
                                </span>
                            </button>
                        </template>
                    </div>

                    {{-- PELANGGAN TERPILIH --}}
                    <div
                        class="booking-selected-customer"
                        x-show="selectedCustomer"
                        x-cloak
                    >
                        <div>
                            <strong x-text="isPublicBooking ? 'Pelanggan terdaftar' : selectedCustomer?.nama"></strong>

                            <span x-show="!isPublicBooking" x-text="selectedCustomer?.no_wa"></span>

                            <small>Pelanggan terdaftar</small>
                        </div>

                        <button
                            type="button"
                            @click="clearCustomer()"
                        >
                            Ganti
                        </button>
                    </div>

                    {{-- FORM HANYA UNTUK PELANGGAN BARU --}}
                    <div
                        x-show="isNewCustomer"
                        x-cloak
                    >
                        <div class="booking-divider">
                            <span>Data pelanggan baru</span>
                        </div>

                        <div class="booking-form-grid">
                            <div class="booking-field booking-field-full">
                                <label for="customer-name">
                                    Nama Lengkap *
                                </label>

                                <input
                                    id="customer-name"
                                    type="text"
                                    x-model="customerName"
                                    placeholder="Masukkan nama lengkap"
                                    autocomplete="name"
                                >
                            </div>
                        </div>

                        <p class="booking-hint">
                            Nomor WhatsApp sudah diperiksa dan belum terdaftar.
                            Lengkapi nama untuk melanjutkan booking.
                        </p>
                    </div>

                    {{-- PETUNJUK SAAT NOMOR BELUM DIPERIKSA --}}
                    <p
                        class="booking-hint booking-customer-prompt"
                        x-show="!customerLookupDone && !selectedCustomer"
                        x-cloak
                    >
                        Masukkan nomor WhatsApp aktif. Form data hewan dan layanan
                        akan muncul setelah nomor berhasil diperiksa.
                    </p>

                </section>


                {{-- ========================================= --}}
                {{-- 02. DATA HEWAN --}}
                {{-- ========================================= --}}

                <section class="booking-card" x-show="customerReady" x-cloak>

                    <div class="booking-section-heading">
                        <span class="booking-step">02</span>

                        <div>
                            <h2>Data Hewan</h2>
                            <p>
                                Berikan informasi hewan yang akan
                                mendapatkan layanan grooming.
                            </p>
                        </div>
                    </div>

                    {{-- PILIH HEWAN TERDAFTAR --}}
                    <div
                        class="booking-field booking-field-full"
                        x-show="selectedCustomer && customerPets.length > 0"
                        x-cloak
                    >

                        <label for="existing-pet">
                            Pilih Hewan Terdaftar
                        </label>

                        <select
                            id="existing-pet"
                            x-model="selectedPetId"
                            @change="selectExistingPet()"
                        >
                            <option value="">
                                Daftarkan hewan baru
                            </option>

                            <template
                                x-for="existingPet in customerPets"
                                :key="existingPet.id"
                            >
                                <option
                                    :value="String(existingPet.id)"
                                    x-text="existingPet.nama + ' — ' + existingPet.spesies"
                                ></option>
                            </template>

                        </select>

                        <p class="booking-hint">
                            Pilih hewan yang sudah terdaftar atau
                            kosongkan untuk menambahkan hewan baru.
                        </p>

                    </div>

                    {{-- FORM DATA HEWAN --}}
                    <div class="booking-form-grid">

                        <div class="booking-field">

                            <label for="pet-name">
                                Nama Hewan *
                            </label>

                            <input
                                id="pet-name"
                                type="text"
                                x-model="pet.nama"
                                placeholder="Nama hewan"
                            >

                        </div>

                        <div class="booking-field">

                            <label for="pet-species">
                                Spesies *
                            </label>

                            <select
                                id="pet-species"
                                x-model="pet.spesies"
                            >
                                <option value="">Pilih spesies</option>
                                <option value="anjing">Anjing</option>
                                <option value="kucing">Kucing</option>
                            </select>

                        </div>

                        <div class="booking-field">

                            <label for="pet-breed">
                                Ras
                            </label>

                            <input
                                id="pet-breed"
                                type="text"
                                x-model="pet.ras"
                                placeholder="Contoh: Persian, Poodle"
                            >

                        </div>

                        <div class="booking-field">

                            <label for="pet-size">
                                Ukuran *
                            </label>

                            <select
                                id="pet-size"
                                x-model="pet.ukuran"
                            >
                                <option value="">Pilih ukuran</option>

                                <template
                                    x-for="factor in factorsByCategory('ukuran')"
                                    :key="factor.key"
                                >
                                    <option
                                        :value="factor.key"
                                        x-text="factor.label"
                                    ></option>
                                </template>

                            </select>

                        </div>

                        <div class="booking-field">

                            <label for="pet-coat">
                                Jenis Bulu *
                            </label>

                            <select
                                id="pet-coat"
                                x-model="pet.jenis_bulu"
                            >
                                <option value="">Pilih jenis bulu</option>

                                <template
                                    x-for="factor in factorsByCategory('jenis_bulu')"
                                    :key="factor.key"
                                >
                                    <option
                                        :value="factor.key"
                                        x-text="factor.label"
                                    ></option>
                                </template>

                            </select>

                        </div>

                        <div class="booking-field">

                            <label for="pet-behavior">
                                Perilaku *
                            </label>

                            <select
                                id="pet-behavior"
                                x-model="pet.perilaku"
                            >
                                <option value="">Pilih perilaku</option>

                                <template
                                    x-for="factor in factorsByCategory('perilaku')"
                                    :key="factor.key"
                                >
                                    <option
                                        :value="factor.key"
                                        x-text="factor.label"
                                    ></option>
                                </template>

                            </select>

                        </div>

                    </div>

                    {{-- ALERGI --}}
                    <div class="booking-field booking-field-full">

                        <label for="pet-allergy">
                            Alergi
                        </label>

                        <textarea
                            id="pet-allergy"
                            x-model="pet.alergi"
                            rows="2"
                            placeholder="Tuliskan alergi jika ada..."
                        ></textarea>

                    </div>

                    {{-- KESEHATAN --}}
                    <div class="booking-field booking-field-full">

                        <label for="pet-health">
                            Catatan Kesehatan
                        </label>

                        <textarea
                            id="pet-health"
                            x-model="pet.catatan_kesehatan"
                            rows="2"
                            placeholder="Informasi kesehatan hewan..."
                        ></textarea>

                    </div>

                    {{-- KONDISI TAMBAHAN --}}
                    <div class="booking-field booking-field-full">

                        <label>Kondisi Tambahan</label>

                        <p class="booking-hint">
                            Pilih kondisi yang sesuai dengan hewan.
                        </p>

                        <div class="booking-check-grid">

                            <template
                                x-for="factor in factorsByCategory('kondisi')"
                                :key="factor.key"
                            >
                                <label class="booking-check-item">

                                    <input
                                        type="checkbox"
                                        :value="factor.key"
                                        x-model="conditions"
                                    >

                                    <span x-text="factor.label"></span>

                                </label>
                            </template>

                        </div>

                    </div>

                </section>


                {{-- ========================================= --}}
                {{-- 03. PILIH LAYANAN --}}
                {{-- ========================================= --}}

                <section class="booking-card" x-show="customerReady" x-cloak>

                    <div class="booking-section-heading">
                        <span class="booking-step">03</span>

                        <div>
                            <h2>Pilih Layanan</h2>
                            <p>
                                Pilih satu atau beberapa layanan
                                grooming sesuai kebutuhan hewan.
                            </p>
                        </div>
                    </div>

                    <div class="booking-service-list">

                        <template
                            x-for="service in services"
                            :key="service.id"
                        >

                            <label
                                class="booking-service-option"
                                :class="{
    'is-selected': selectedServices.some(
        id => String(id) === String(service.id)
    )
}"
                            >

                                <input
    type="checkbox"
    :value="Number(service.id)"
    x-model="selectedServices"
    @change="resetEstimation()"
>

                                <span class="booking-service-info">

                                    <strong x-text="service.nama"></strong>

                                    <small>
                                        <span x-text="service.durasi"></span>
                                        menit
                                    </small>

                                </span>

                                <strong
                                    class="booking-service-price"
                                    x-text="formatCurrency(service.harga)"
                                ></strong>

                            </label>

                        </template>

                        <p
                            class="booking-empty"
                            x-show="services.length === 0"
                        >
                            Belum ada layanan aktif.
                        </p>

                    </div>

                    <button
                        type="button"
                        class="booking-primary-button"
                        @click="calculateDuration()"
                        :disabled="loadingEstimation"
                    >
                        <span x-show="!loadingEstimation">
                            Hitung Estimasi Durasi
                        </span>

                        <span x-show="loadingEstimation">
                            Menghitung...
                        </span>
                    </button>

                    {{-- HASIL ESTIMASI --}}
                    <div
                        class="booking-estimation"
                        x-show="estimation"
                        x-cloak
                    >
                        <div class="booking-estimation-header">
                            <span>Estimasi waktu pengerjaan</span>
                            <span class="booking-estimation-icon">✦</span>
                        </div>

                        <div class="booking-estimation-total">
                            <strong x-text="estimation?.durasi_menit ?? 0"></strong>
                            <span>menit</span>
                        </div>

                        <p class="booking-estimation-description">
                            Estimasi durasi dihitung berdasarkan ukuran hewan,
                            jenis bulu, perilaku, layanan, dan kondisi hewan.
                        </p>

                        <div class="booking-estimation-note">
                            <span>✓</span>
                            <span>
                                Estimasi waktu dapat menyesuaikan kondisi hewan
                                saat proses grooming berlangsung.
                            </span>
                        </div>
                    </div>

                </section>



{{-- ========================================= --}}
{{-- 04. PILIH JADWAL --}}
{{-- ========================================= --}}

<section class="booking-card booking-schedule-card">

    <div class="booking-section-heading">
        <span class="booking-step">04</span>

        <div>
            <h2>Pilih Jadwal</h2>
            <p>
                Pilih tanggal dan jam grooming yang paling sesuai
                dengan waktumu.
            </p>
        </div>
    </div>

    {{-- KETERANGAN MINGGU --}}
    <div class="booking-week-heading">
        <div>
            <strong>Jadwal 7 Hari ke Depan</strong>
            <span>Pilih salah satu tanggal yang tersedia.</span>
        </div>

        <span class="booking-week-badge">
            <span></span>
            Live availability
        </span>
    </div>

    {{-- PILIH TANGGAL MANUAL --}}
    <div class="booking-field booking-manual-date">
        <label for="booking-manual-date">Atau pilih tanggal secara manual</label>
        <input
            id="booking-manual-date"
            type="date"
            x-model="bookingDate"
            :min="today"
            :disabled="!estimation || loadingSlots"
            @change="selectBookingDate($event.target.value)"
        >
        <p class="booking-hint">
            Pilih tanggal lain di luar rekomendasi mingguan. Ketersediaan akan diperiksa langsung oleh sistem.
        </p>
    </div>

    {{-- LOADING --}}
    <div
        class="booking-week-loading"
        x-show="loadingSlots"
        x-cloak
    >
        <span class="booking-loading-spinner"></span>
        <span>Memeriksa ketersediaan jadwal...</span>
    </div>

    {{-- KALENDER 7 HARI --}}
    <div
        class="booking-week-grid"
        x-show="!loadingSlots && estimation"
        x-cloak
    >

        <template
            x-for="day in weeklySchedule"
            :key="day.date"
        >

            <button
                type="button"
                class="booking-day-option"

                :class="{
                    'is-selected': bookingDate === day.date,
                    'is-available': day.slots.length > 0,
                    'is-unavailable': day.slots.length === 0
                }"

                @click="selectBookingDate(day.date)"
            >

                <span
                    class="booking-day-name"
                    x-text="formatWeekDay(day.date)"
                ></span>

                <strong
                    class="booking-day-number"
                    x-text="formatDayNumber(day.date)"
                ></strong>

                <span
                    class="booking-day-month"
                    x-text="formatMonth(day.date)"
                ></span>

                <span
                    class="booking-day-status"
                    x-text="day.slots.length
                        ? day.slots.length + ' jam tersedia'
                        : 'Belum tersedia'"
                ></span>

                <span
                    class="booking-week-badge"
                    x-show="isRecommendedDay(day.date)"
                    x-cloak
                >
                    Saran sistem
                </span>

            </button>

        </template>

    </div>

    {{-- KETERANGAN TANGGAL --}}
    <div
        class="booking-selected-date"
        x-show="bookingDate && estimation"
        x-cloak
    >
        <div>
            <span>Jadwal untuk tanggal</span>

            <strong x-text="formatLongDate(bookingDate)"></strong>
        </div>

        <span class="booking-selected-date-icon">✦</span>
    </div>

    {{-- SLOT JAM --}}
    <div
        class="booking-slot-area booking-week-slot-area"
        x-show="!loadingSlots && estimation"
        x-cloak
    >

        <div class="booking-slot-heading">
            <strong>Pilih Jam Kedatangan</strong>

            <span x-show="slots.length">
                <span x-text="slots.length"></span> jadwal tersedia
            </span>
        </div>

        <div
            class="booking-slot-grid booking-week-slot-grid"
            x-show="slots.length"
        >

            <template
                x-for="slot in slots"
                :key="slot.mulai"
            >

                <button
                    type="button"
                    class="booking-slot"

                    :class="{
                        'is-selected':
                            selectedSlot?.mulai === slot.mulai
                    }"

                    @click="selectSlot(slot)"
                >

                    <span class="booking-slot-time">
                        <span>◷</span>

                        <strong x-text="formatTime(slot.mulai)"></strong>
                    </span>

                    <small>Waktu tersedia</small>

                </button>

            </template>

        </div>

        {{-- JIKA TIDAK ADA SLOT --}}
        <div
            class="booking-no-slot"
            x-show="!slots.length"
        >
            <span class="booking-no-slot-icon">♡</span>

            <strong>Belum ada jadwal tersedia</strong>

            <p
                x-text="slotMessage || 'Silakan pilih tanggal lainnya.'"
            ></p>
        </div>

    </div>

    {{-- PETUNJUK --}}
    <div class="booking-schedule-hint">
        <span>ⓘ</span>
        <p>
            Rekomendasi mingguan diurutkan berdasarkan jumlah jam kosong
            yang ditemukan sistem. Identitas groomer tidak ditampilkan pada
            halaman publik dan akan ditentukan kembali saat booking dikonfirmasi.
        </p>
    </div>

</section>



                {{-- ========================================= --}}
                {{-- 05. KONFIRMASI BOOKING --}}
                {{-- ========================================= --}}

                <section class="booking-card" x-show="customerReady" x-cloak>

                    <div class="booking-section-heading">
                        <span class="booking-step">05</span>

                        <div>
                            <h2>Konfirmasi Booking</h2>
                            <p>
                                Periksa kembali informasi booking
                                sebelum mengirimkan pesanan.
                            </p>
                        </div>
                    </div>

                    <div class="booking-field">

                        <label for="booking-note">
                            Catatan Tambahan
                        </label>

                        <textarea
                            id="booking-note"
                            x-model="customerNote"
                            rows="3"
                            maxlength="500"
                            placeholder="Tuliskan pesan atau kebutuhan khusus..."
                        ></textarea>

                    </div>

                    <div
                        class="booking-error"
                        x-show="errorMessage"
                        x-text="errorMessage"
                        x-cloak
                    ></div>

                    <button
                        type="button"
                        class="booking-submit-button"
                        @click="submitBooking()"
                        :disabled="submitting || !selectedSlot"
                    >

                        <span x-show="!submitting">
                            Konfirmasi Booking
                        </span>

                        <span x-show="submitting">
                            Menyimpan Booking...
                        </span>

                    </button>

                </section>

            </div>


            {{-- ========================================= --}}
            {{-- RINGKASAN BOOKING --}}
            {{-- ========================================= --}}

            <aside class="booking-summary">

                <div class="booking-summary-card">

                    <h2>Ringkasan Booking</h2>

                    <p>
                        Periksa detail booking sebelum disimpan.
                    </p>

                    <p
                        class="booking-hint"
                        x-show="!customerReady"
                        x-cloak
                    >
                        Ringkasan akan terisi setelah data pelanggan diperiksa.
                    </p>

                    <div class="booking-summary-item">
                        <span>Pelanggan</span>

                        <strong
                            x-text="selectedCustomer?.nama || customerName || 'Belum diisi'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Hewan</span>

                        <strong
                            x-text="pet.nama || 'Belum diisi'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Spesies</span>

                        <strong
                            x-text="pet.spesies || '-'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Layanan</span>

                        <strong
                            x-text="selectedServiceNames.length
                                ? selectedServiceNames.join(', ')
                                : 'Belum dipilih'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Estimasi Durasi</span>

                        <strong
                            x-text="estimation
                                ? estimation.durasi_menit + ' menit'
                                : '-'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Tanggal</span>

                        <strong x-text="bookingDate ? formatLongDate(bookingDate) : '-'"></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Waktu</span>

                        <strong
                            x-text="selectedSlot
                                ? formatTime(selectedSlot.mulai)
                                : '-'"
                        ></strong>
                    </div>

                    <div class="booking-summary-item">
                        <span>Penugasan Groomer</span>

                        <strong
                            x-text="selectedSlot?.nama_groomer || 'Ditentukan sistem'"
                        ></strong>
                    </div>

                    <div class="booking-summary-total">
                        <span>Total Layanan</span>

                        <strong x-text="formatCurrency(totalPrice)"></strong>
                    </div>

                    <p class="booking-summary-note">
                        Total dihitung dari layanan yang dipilih.
                        Ketersediaan jadwal diperiksa kembali saat booking disimpan.
                    </p>

                </div>

            </aside>

        </div>


        {{-- ========================================= --}}
        {{-- HASIL BOOKING --}}
        {{-- ========================================= --}}

        {{-- HASIL BOOKING --}}
<div
    class="booking-success-overlay"
    x-show="bookingResult"
    x-cloak
>
    <div class="booking-success-card">

        <div class="booking-success-icon">✓</div>

        <h2>Booking Berhasil!</h2>

        <p>
            Terima kasih telah memilih Pet Care Ops.
            Simpan kode booking untuk memeriksa status jadwal grooming kamu.
        </p>

        <div
            class="booking-success-details"
            x-show="bookingResult"
        >
            <span>Token Booking</span>
            <strong
                class="booking-success-token"
                x-text="bookingResult?.token"
            ></strong>

            <span>Hewan</span>
            <strong x-text="bookingResult?.pet || '-'"></strong>

            <span>Waktu</span>
            <strong x-text="bookingResult?.mulai || '-'"></strong>

            <span>Durasi</span>
            <strong
                x-text="(bookingResult?.durasi_estimasi || 0) + ' menit'"
            ></strong>

            {{-- RINCIAN LAYANAN --}}
            <div class="booking-success-service-heading">
                Layanan yang Dipilih
            </div>

            <template
                x-for="(service, index) in (bookingResult?.layanan || [])"
                :key="index"
            >
                <div class="booking-success-service-row">
                    <span x-text="service.nama"></span>

                    <strong
                        x-text="formatCurrency(service.harga)"
                    ></strong>
                </div>
            </template>

            {{-- TOTAL BIAYA --}}
            <div class="booking-success-total">
                <span>Total Biaya</span>

                <strong
                    x-text="formatCurrency(
                        (bookingResult?.layanan || []).reduce(
                            (total, service) =>
                                total + Number(service.harga || 0),
                            0
                        )
                    )"
                ></strong>
            </div>
        </div>

        <button
            type="button"
            class="booking-copy-status-button"
            @click="copyStatusLink()"
        >
            <span x-text="statusLinkCopied ? 'Tautan berhasil disalin' : 'Salin tautan status'"></span>
        </button>

        <a
            class="booking-primary-button"
            :href="bookingResult?.status_url || '{{ route('home') }}'"
        >
            Lihat Status Booking
        </a>

    </div>
</div>

    </section>

</div>

@endsection
