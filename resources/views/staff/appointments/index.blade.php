<x-app-layout>
    <x-slot name="header">Appointment</x-slot>

    <section class="appointments-page" x-data="appointmentManager">
        <div class="appointments-intro">
            <div>
                <span class="appointments-eyebrow">OPERASIONAL</span>
                <h1 class="appointments-title">Daftar Appointment</h1>
                <p class="appointments-description">Pantau jadwal, layanan, dan progres grooming pelanggan.</p>
            </div>
            <a href="{{ route('staf.booking.create') }}" class="appointments-add-button">＋ Buat Walk-in</a>
        </div>

        <div class="appointments-summary">
            <article><span>Total Appointment</span><strong>{{ number_format($summary['total']) }}</strong><small>Seluruh data</small></article>
            <article><span>Menunggu</span><strong>{{ number_format($summary['menunggu']) }}</strong><small>Belum dikerjakan</small></article>
            <article><span>Dikerjakan</span><strong>{{ number_format($summary['dikerjakan']) }}</strong><small>Sedang grooming</small></article>
            <article><span>Selesai</span><strong>{{ number_format($summary['selesai']) }}</strong><small>Menunggu dijemput / selesai</small></article>
        </div>

        <form method="GET" action="{{ route('staf.appointments.index') }}" class="appointments-filters">
            <label class="appointments-search">
                <span>Cari appointment</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="ID, nama pemilik, atau hewan">
            </label>
            <label>
                <span>Tanggal</span>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}">
            </label>
            <label>
                <span>Groomer</span>
                <select name="groomer_id">
                    <option value="">Semua groomer</option>
                    @foreach($groomers as $groomer)
                        <option value="{{ $groomer->id }}" @selected((string)request('groomer_id') === (string)$groomer->id)>
                            {{ $groomer->user?->name ?? 'Groomer #'.$groomer->id }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Status</span>
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach(['menunggu'=>'Menunggu','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai','dijemput'=>'Dijemput','dibatalkan'=>'Dibatalkan'] as $key=>$label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="appointments-filter-button">Terapkan</button>
            <a href="{{ route('staf.appointments.index') }}" class="appointments-reset-button">Reset</a>
        </form>

        <div class="appointments-feedback" x-show="message" x-cloak x-text="message"></div>

        <div class="appointments-panel">
            <div class="appointments-panel-header">
                <div><h2>Semua Appointment</h2><p>{{ $appointments->total() }} data ditemukan</p></div>
                <span class="appointments-total-badge">Halaman {{ $appointments->currentPage() }} / {{ $appointments->lastPage() }}</span>
            </div>

            @if($appointments->isNotEmpty())
                <div class="appointments-table-wrapper">
                    <table class="appointments-table">
                        <thead><tr><th>Kode</th><th>Jadwal</th><th>Pemilik & Hewan</th><th>Layanan</th><th>Groomer</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                        @foreach($appointments as $appointment)
                            @php($status = $appointment->status ?? 'unknown')
                            <tr data-appointment-row="{{ $appointment->id }}">
                                <td><strong>#{{ $appointment->id }}</strong><small class="appointments-source">{{ ucfirst($appointment->sumber ?? 'booking') }}</small></td>
                                <td>{{ $appointment->mulai?->format('d M Y') ?? '-' }}<small>{{ $appointment->mulai?->format('H:i') ?? '-' }} WIB</small></td>
                                <td><strong>{{ $appointment->pet?->customer?->nama ?? 'Pemilik tidak tersedia' }}</strong><small>{{ $appointment->pet?->nama ?? 'Hewan tidak tersedia' }}</small></td>
                                <td><div class="appointments-service-list">@forelse($appointment->services as $service)<span>{{ $service->nama }}</span>@empty<small>-</small>@endforelse</div></td>
                                <td>{{ $appointment->groomer?->user?->name ?? 'Belum ditentukan' }}</td>
                                <td><span class="appointments-status appointments-status--{{ \Illuminate\Support\Str::slug($status) }}" data-status>{{ \Illuminate\Support\Str::headline($status) }}</span></td>
                                <td>
                                    <div class="appointments-actions">
                                        @if($status === 'menunggu')
                                            <button type="button" class="appointments-action appointments-action--start" @click="changeStatus({{ $appointment->id }}, 'dikerjakan')">Mulai</button>
                                            <button type="button" class="appointments-action appointments-action--cancel" @click="openCancel({{ $appointment->id }})">Batalkan</button>
                                        @elseif($status === 'dikerjakan')
                                            <button type="button" class="appointments-action appointments-action--complete" @click="changeStatus({{ $appointment->id }}, 'selesai')">Selesai</button>
                                        @elseif($status === 'selesai')
                                            <button type="button" class="appointments-action appointments-action--pickup" @click="changeStatus({{ $appointment->id }}, 'dijemput')">Sudah dijemput</button>
                                        @else
                                            <span class="appointments-muted">Tidak ada aksi</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="appointments-pagination">{{ $appointments->links() }}</div>
            @else
                <div class="appointments-empty"><span>🐾</span><h3>Belum ada appointment</h3><p>Coba ubah filter atau buat booking walk-in baru.</p></div>
            @endif
        </div>

        <div class="appointments-modal-backdrop" x-show="cancelModal" x-cloak @keydown.escape.window="cancelModal = false">
            <section class="appointments-modal" role="dialog" aria-modal="true" aria-labelledby="cancel-title">
                <button type="button" class="appointments-modal-close" @click="cancelModal = false" aria-label="Tutup">×</button>
                <span class="appointments-eyebrow">KONFIRMASI</span>
                <h2 id="cancel-title">Batalkan appointment?</h2>
                <p>Appointment hanya dapat dibatalkan saat statusnya masih menunggu.</p>
                <label>Alasan pembatalan (opsional)<textarea x-model="cancelReason" maxlength="500" placeholder="Tuliskan alasan pembatalan..."></textarea></label>
                <small class="appointments-modal-note">Catatan: alasan belum disimpan ke database karena belum tersedia kolom khusus.</small>
                <div class="appointments-modal-actions">
                    <button type="button" class="appointments-reset-button" @click="cancelModal = false">Kembali</button>
                    <button type="button" class="appointments-action appointments-action--cancel" :disabled="busy" @click="confirmCancel()">Ya, batalkan</button>
                </div>
            </section>
        </div>
    </section>
</x-app-layout>
