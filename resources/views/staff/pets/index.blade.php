<x-app-layout>
    <x-slot name="header"><h1 class="m5-page-title">Data Hewan</h1></x-slot>
    <section class="m5-page">
        <div class="m5-page-heading"><div><span class="m5-eyebrow">DATA PELANGGAN</span><h2>Daftar Hewan</h2><p>Pilih hewan untuk melihat riwayat grooming sebelumnya.</p></div></div>
        <section class="m5-panel">
            @if($pets->count())
                <div class="m5-pet-grid">
                    @foreach($pets as $pet)
                        <article class="m5-pet-card">
                            <span class="m5-pet-avatar">{{ strtoupper(mb_substr($pet->nama, 0, 1)) }}</span>
                            <div class="m5-pet-card-info"><h3>{{ $pet->nama }}</h3><p>{{ ucfirst($pet->spesies) }}{{ $pet->ras ? ' · '.$pet->ras : '' }}</p><span>Pemilik: {{ $pet->customer?->nama ?? '-' }}</span></div>
                            <a class="m5-button m5-button--light" href="{{ route('staf.pets.grooming-history', $pet) }}">Riwayat grooming</a>
                        </article>
                    @endforeach
                </div>
                <div class="m5-pagination">{{ $pets->links() }}</div>
            @else
                <div class="m5-empty"><span class="m5-empty-icon">♡</span><h3>Belum ada data hewan</h3><p>Data hewan pelanggan akan tampil di sini setelah ditambahkan.</p></div>
            @endif
        </section>
    </section>
</x-app-layout>
