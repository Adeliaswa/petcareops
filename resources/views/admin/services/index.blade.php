<x-app-layout>
    <x-slot name="header">Layanan grooming</x-slot>
    @php
        $masterConfig = [
            'kind' => 'service', 'title' => 'Layanan grooming', 'singular' => 'Layanan grooming',
            'description' => 'Atur layanan, durasi dasar, harga, dan status layanan.', 'api' => '/admin/layanan', 'canCreate'=>true,'canDelete'=>true,
            'fields' => [['name'=>'nama','label'=>'Nama layanan','type'=>'text','required'=>true],['name'=>'deskripsi','label'=>'Deskripsi','type'=>'textarea','required'=>false],['name'=>'durasi_dasar','label'=>'Durasi dasar (menit)','type'=>'number','required'=>true],['name'=>'harga','label'=>'Harga (Rp)','type'=>'number','required'=>true],['name'=>'aktif','label'=>'Layanan aktif','type'=>'checkbox','required'=>false]],
            'columns' => [['key'=>'nama','label'=>'Nama layanan','type'=>''],['key'=>'durasi_dasar','label'=>'Durasi','type'=>'number'],['key'=>'harga','label'=>'Harga','type'=>'currency'],['key'=>'aktif','label'=>'Status','type'=>'badge']],
        ];
    @endphp
    @include('shared.master-crud')
</x-app-layout>
