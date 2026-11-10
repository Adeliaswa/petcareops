@extends('layouts.public')


@section('title', 'Booking Grooming | Pet Care Ops')
@section('description', 'Pilih hewan, layanan, estimasi durasi, dan jadwal grooming.')


@section('content')
<div class="public-booking-shell">
    <header class="public-header public-header--compact">
        <a href="{{ route('home') }}" class="public-brand">
            <img src="{{ asset('images/petcare-logo.svg') }}" alt="">
            <span><strong>Pet Care Ops</strong><small>Pet Grooming &amp; Spa</small></span>
        </a>
        <a class="public-text-link" href="{{ route('home') }}">← Kembali ke beranda</a>
    </header>
    <div class="public-booking-heading">
        <span class="public-eyebrow">ONLINE BOOKING</span>
        <h1>Booking Grooming</h1>
        <p>Lengkapi data hewan, pilih layanan, lalu temukan jadwal yang tersedia.</p>
    </div>
    @php
        $bookingConfig = [
            'services' => services->map(fn(service) => [
                'id' => $service->id,
                'nama' => $service->nama,
                'harga' => $service->harga,
                'durasi' => $service->durasi_dasar,
            ])->values(),
            'factors' => factors->map(fn(factor) => [
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
        x-init="init()"
    >
        <script type="application/json" data-booking-config>
            {!! json_encode(
                $bookingConfig,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
            ) !!}
        </script>


        <div class="booking-intro">
            <div>
                <span class="booking-eyebrow">BOOKING ONLINE</span>
                <h1 class="booking-title">Buat Booking Baru</h1>
                <p class="booking-description">
                    Catat kedatangan pelanggan dan atur jadwal grooming hewan.
                </p>
            </div>


            <a href="{{ route('home') }}"
               class="booking-back-button">
                Kembali ke Beranda
            </a>
        </div>


        <div class="booking-layout">


            <div class="booking-main">


                {{-- 1. DATA PELANGGAN --}}
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <span class="booking-step">01</span>
                        <div>
                            <h2>Data Pelanggan</h2>
                            <p>Cari pelanggan lama atau masukkan pelanggan baru.</p>
                        </div>
                    </div>


                    <div class="booking-field">
                        <label for="booking-search">Cari Pelanggan</label>


                        <input
                            id="booking-search"
                            type="search"
                            placeholder="Masukkan nama atau nomor WhatsApp..."
                            x-model="customerQuery"
                            @input.debounce.400ms="searchCustomers()"
                            autocomplete="off"
                        >


                        <div class="booking-search-results"
                             x-show="customerResults.length > 0"
                             x-cloak>
                            <template x-for="customer in customerResults"
                                      :key="customer.id">
                                <button
                                    type="button"
                                    class="booking-search-result"
                                    @click="selectCustomer(customer)"
                                >
                                    <span>
                                        <strong x-text="customer.nama"></strong>
                                        <small x-text="customer.no_wa"></small>
                                    </span>
                                    <span class="booking-select-label">Pilih</span>
                                </button>
                            </template>
                        </div>


                        <p class="booking-hint"
                           x-show="searchMessage"
                           x-text="searchMessage"></p>
                    </div>


                    <div class="booking-selected-customer"
                         x-show="selectedCustomer"
                         x-cloak>
                        <div>
                            <strong x-text="selectedCustomer?.nama"></strong>
                            <span x-text="selectedCustomer?.no_wa"></span>
                        </div>
                        <button type="button"
                                @click="clearCustomer()">
                            Ganti
                        </button>
                    </div>


                    <div x-show="!selectedCustomer" x-cloak>
                        <div class="booking-divider">
                            <span>atau masukkan pelanggan baru</span>
                        </div>


                        <div class="booking-form-grid">
                            <div class="booking-field">
                                <label for="customer-name">Nama Pelanggan *</label>
                                <input
                                    id="customer-name"
                                    type="text"
                                    x-model="customerName"
                                    placeholder="Nama lengkap pelanggan"
                                >
                            </div>


                            <div class="booking-field">
                                <label for="customer-phone">Nomor WhatsApp *</label>
                                <input
                                    id="customer-phone"
                                    type="tel"
                                    x-model="customerPhone"
                                    placeholder="Contoh: 081234567890"
                                >
                            </div>
                        </div>
                    </div>
                </section>




                    <div class="booking-field booking-field-full" x-show="selectedCustomer && customerPets.length" x-cloak>
                        <label for="existing-pet">Pilih hewan terdaftar</label>
                        <select id="existing-pet" x-model="selectedPetId" @change="selectExistingPet()">
                            <option value="">Daftarkan hewan baru</option>
                            <template x-for="existingPet in customerPets" :key="existingPet.id">
                                <option :value="String(existingPet.id)" x-text="existingPet.nama + ' — ' + existingPet.spesies"></option>
                            </template>
                        </select>
                        <p class="booking-hint">Pilih hewan yang sudah terdaftar atau kosongkan untuk menambahkan hewan baru.</p>
                    </div>


                {{-- 2. DATA HEWAN --}}
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <span class="booking-step">02</span>
                        <div>
                            <h2>Data Hewan</h2>
                            <p>Masukkan informasi hewan yang akan mendapatkan layanan.</p>
                        </div>
                    </div>


                    <div class="booking-form-grid">
                        <div class="booking-field">
                            <label for="pet-name">Nama Hewan *</label>
                            <input
                                id="pet-name"
                                type="text"
                                x-model="pet.nama"
                                placeholder="Nama hewan"
                            >
                        </div>


                        <div class="booking-field">
                            <label for="pet-species">Spesies *</label>
                            <select id="pet-species" x-model="pet.spesies">
                                <option value="">Pilih spesies</option>
                                <option value="anjing">Anjing</option>
                                <option value="kucing">Kucing</option>
                            </select>
                        </div>


                        <div class="booking-field">
                            <label for="pet-breed">Ras</label>
                            <input
                                id="pet-breed"
                                type="text"
                                x-model="pet.ras"
                                placeholder="Ras hewan"
                            >
                        </div>


                        <div class="booking-field">
                            <label for="pet-size">Ukuran *</label>
                            <select id="pet-size" x-model="pet.ukuran">
                                <option value="">Pilih ukuran</option>
                                <template x-for="factor in factorsByCategory('ukuran')"
                                          :key="factor.key">
                                    <option :value="factor.key"
                                            x-text="factor.label"></option>
                                </template>
                            </select>
                        </div>


                        <div class="booking-field">
                            <label for="pet-coat">Jenis Bulu *</label>
                            <select id="pet-coat" x-model="pet.jenis_bulu">
                                <option value="">Pilih jenis bulu</option>
                                <template x-for="factor in factorsByCategory('jenis_bulu')"
                                          :key="factor.key">
                                    <option :value="factor.key"
                                            x-text="factor.label"></option>
                                </template>
                            </select>
                        </div>


                        <div class="booking-field">
                            <label for="pet-behavior">Perilaku *</label>
                            <select id="pet-behavior" x-model="pet.perilaku">
                                <option value="">Pilih perilaku</option>
                                <template x-for="factor in factorsByCategory('perilaku')"
                                          :key="factor.key">
                                    <option :value="factor.key"
                                            x-text="factor.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>


                    <div class="booking-field booking-field-full">
                        <label for="pet-allergy">Alergi</label>
                        <textarea
                            id="pet-allergy"
                            x-model="pet.alergi"
                            rows="2"
                            placeholder="Tuliskan alergi jika ada..."
                        ></textarea>
                    </div>


                    <div class="booking-field booking-field-full">
                        <label for="pet-health">Catatan Kesehatan</label>
                        <textarea
                            id="pet-health"
                            x-model="pet.catatan_kesehatan"
                            rows="2"
                            placeholder="Informasi kesehatan hewan..."
                        ></textarea>
                    </div>


                    <div class="booking-field booking-field-full">
                        <label>Kondisi Tambahan</label>
                        <p class="booking-hint">
                            Pilih kondisi yang sesuai dengan hewan.
                        </p>


                        <div class="booking-check-grid">
                            <template x-for="factor in factorsByCategory('kondisi')"
                                      :key="factor.key">
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


                {{-- 3. LAYANAN --}}
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <span class="booking-step">03</span>
                        <div>
                            <h2>Pilih Layanan</h2>
                            <p>Pilih satu atau beberapa layanan grooming.</p>
                        </div>
                    </div>


                    <div class="booking-service-list">
                        <template x-for="service in services"
                                  :key="service.id">
                            <label class="booking-service-option"
                                   :class="{ 'is-selected': selectedServices.includes(Number(service.id)) }">


                                <input
                                    type="checkbox"
                                    :value="Number(service.id)"
                                    x-model="selectedServices"
                                    @change="resetEstimation()"
                                >


                                <span class="booking-service-info">
                                    <strong x-text="service.nama"></strong>
                                    <small>
                                        <span x-text="service.durasi"></span> menit
                                    </small>
                                </span>


                                <strong class="booking-service-price"
                                        x-text="formatCurrency(service.harga)">
                                </strong>
                            </label>
                        </template>


                        <p class="booking-empty"
                           x-show="services.length === 0">
                            Belum ada layanan aktif.
                        </p>
                    </div>


                    <button
                        type="button"
                        class="booking-primary-button"
                        @click="calculateDuration()"
                        :disabled="loadingEstimation"
                    >
                        <span x-show="!loadingEstimation">Hitung Estimasi Durasi</span>
                        <span x-show="loadingEstimation">Menghitung...</span>
                    </button>


                    <div class="booking-estimation"
                         x-show="estimation"
                         x-cloak>
                        <span>Estimasi pengerjaan</span>
                        <strong>
                            <span x-text="estimation?.durasi_menit"></span> menit
                        </strong>
                        <div class="booking-estimation-details">
                            <template x-for="(detail, index) in (estimation?.rincian || [])"
                                      :key="index">
                                <span x-text="typeof detail === 'string' ? detail : JSON.stringify(detail)"></span>
                            </template>
                        </div>
                    </div>
                </section>


                {{-- 4. JADWAL --}}
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <span class="booking-step">04</span>
                        <div>
                            <h2>Pilih Jadwal</h2>
                            <p>Tentukan tanggal dan waktu kedatangan.</p>
                        </div>
                    </div>


                    <div class="booking-field">
                        <label for="booking-date">Tanggal Booking *</label>
                        <input
                            id="booking-date"
                            type="date"
                            x-model="bookingDate"
                            @change="findSlots()"
                            :min="today"
                        >
                    </div>


                    <div class="booking-slot-area">
                        <div class="booking-slot-heading">
                            <strong>Slot Tersedia</strong>
                            <span x-show="loadingSlots">Mencari slot...</span>
                        </div>


                        <div class="booking-slot-grid">
                            <template x-for="slot in slots"
                                      :key="slot.mulai">
                                <button
                                    type="button"
                                    class="booking-slot"
                                    :class="{ 'is-selected': selectedSlot?.mulai === slot.mulai }"
                                    @click="selectSlot(slot)"
                                >
                                    <strong x-text="formatTime(slot.mulai)"></strong>
                                    <small x-text="slot.nama_groomer || 'Groomer tersedia'"></small>
                                </button>
                            </template>
                        </div>


                        <p class="booking-empty"
                           x-show="slotMessage"
                           x-text="slotMessage"></p>
                    </div>
                </section>


                {{-- 5. CATATAN DAN KONFIRMASI --}}
                <section class="booking-card">
                    <div class="booking-section-heading">
                        <span class="booking-step">05</span>
                        <div>
                            <h2>Konfirmasi Booking</h2>
                            <p>Periksa kembali data sebelum menyimpan.</p>
                        </div>
                    </div>


                    <div class="booking-field">
                        <label for="booking-note">Catatan Pelanggan</label>
                        <textarea
                            id="booking-note"
                            x-model="customerNote"
                            rows="3"
                            maxlength="500"
                            placeholder="Catatan tambahan untuk booking..."
                        ></textarea>
                    </div>


                    <div class="booking-error"
                         x-show="errorMessage"
                         x-text="errorMessage"
                         x-cloak></div>


                    <button
                        type="button"
                        class="booking-submit-button"
                        @click="submitBooking()"
                        :disabled="submitting || !selectedSlot"
                    >
                        <span x-show="!submitting">Konfirmasi Booking</span>
                        <span x-show="submitting">Menyimpan Booking...</span>
                    </button>
                </section>
            </div>


            {{-- RINGKASAN --}}
            <aside class="booking-summary">
                <div class="booking-summary-card">
                    <h2>Ringkasan Booking</h2>
                    <p>Periksa detail booking sebelum disimpan.</p>


                    <div class="booking-summary-item">
                        <span>Pelanggan</span>
                        <strong x-text="selectedCustomer?.nama || customerName || 'Belum diisi'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Hewan</span>
                        <strong x-text="pet.nama || 'Belum diisi'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Spesies</span>
                        <strong x-text="pet.spesies || '-'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Jumlah Layanan</span>
                        <strong x-text="selectedServices.length"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Estimasi Durasi</span>
                        <strong x-text="estimation ? estimation.durasi_menit + ' menit' : '-'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Tanggal</span>
                        <strong x-text="bookingDate || '-'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Waktu</span>
                        <strong x-text="selectedSlot ? formatTime(selectedSlot.mulai) : '-'"></strong>
                    </div>


                    <div class="booking-summary-item">
                        <span>Groomer</span>
                        <strong x-text="selectedSlot?.nama_groomer || 'Otomatis'"></strong>
                    </div>


                    <div class="booking-summary-total">
                        <span>Total Layanan</span>
                        <strong x-text="formatCurrency(totalPrice)"></strong>
                    </div>


                    <p class="booking-summary-note">
                        Harga merupakan akumulasi layanan yang dipilih.
                        Konfirmasi tetap mengikuti pemeriksaan ketersediaan sistem.
                    </p>
                </div>
            </aside>


        </div>


        {{-- HASIL BOOKING --}}
        <div class="booking-success-overlay"
             x-show="bookingResult"
             x-cloak>
            <div class="booking-success-card">
                <div class="booking-success-icon">✓</div>
                <h2>Booking Berhasil!</h2>
                <p>Booking grooming berhasil dibuat.</p>


                <div class="booking-success-details"
                     x-show="bookingResult">
                    <span>Token Booking</span>
                    <strong x-text="bookingResult?.token"></strong>
                    <span>Hewan</span>
                    <strong x-text="bookingResult?.pet"></strong>
                    <span>Waktu</span>
                    <strong x-text="bookingResult?.mulai"></strong>
                    <span>Durasi</span>
                    <strong x-text="bookingResult?.durasi_estimasi + ' menit'"></strong>
                </div>


                <a href="{{ route('home') }}"
                   class="booking-primary-button">
                    Lihat Appointment
                </a>
            </div>
        </div>
    </section>


</div>
@endsection