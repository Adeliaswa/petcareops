@extends('layouts.public')

@section('title', 'Status Booking | Pet Care Ops')
@section('content')
<div class="status-page" x-data="bookingStatus" x-init="init(@js(route('booking.status.data', $token)))">
    <header class="public-header public-header--compact">
        <a href="{{ route('home') }}" class="public-brand"><img src="{{ asset('images/petcare-logo.svg') }}" alt=""><span><strong>Pet Care Ops</strong><small>Pet Grooming &amp; Spa</small></span></a>
        <a class="public-text-link" href="{{ route('home') }}">Beranda</a>
    </header>
    <main class="status-card">
        <span class="public-eyebrow">BOOKING INFORMATION</span>
        <h1>Status Booking</h1>
        <p class="status-intro">Informasi jadwal grooming kamu ditampilkan di bawah ini.</p>
        <div x-show="loading" class="status-state">Memuat informasi booking...</div>
        <div x-show="error" x-cloak class="status-error" x-text="error"></div>
        <template x-if="booking">
            <div>
                <div class="status-banner" :class="'status-banner--' + booking.status">
                    <span>Status saat ini</span><strong x-text="statusLabel(booking.status)"></strong>
                </div>
                <div class="status-details">
                    <div><span>Nama hewan</span><strong x-text="booking.pet"></strong></div>
                    <div><span>Tanggal</span><strong x-text="formatDate(booking.mulai)"></strong></div>
                    <div><span>Jam mulai</span><strong x-text="formatTime(booking.mulai)"></strong></div>
                    <div><span>Estimasi selesai</span><strong x-text="formatTime(booking.selesai_estimasi)"></strong></div>
                    <div><span>Estimasi durasi</span><strong x-text="booking.durasi_estimasi + ' menit'"></strong></div>
                </div>
                <h2>Layanan yang dipilih</h2>
                <ul class="status-services"><template x-for="service in booking.layanan" :key="service.nama"><li><span x-text="service.nama"></span><strong x-text="formatCurrency(service.harga)"></strong></li></template></ul>
                <div class="status-token"><span>Kode booking</span><strong x-text="booking.token"></strong></div>
            </div>
        </template>
        <a class="public-button" href="{{ route('booking.create') }}">Buat booking baru</a>
    </main>
</div>
@endsection
