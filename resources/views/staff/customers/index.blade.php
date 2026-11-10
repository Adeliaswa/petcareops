<x-app-layout>
    <x-slot name="header">Pemilik</x-slot>
    @php
        $masterConfig = [
            'kind' => 'customer', 'title' => 'Pemilik', 'singular' => 'Pemilik',
            'description' => 'Kelola data pemilik hewan.', 'api' => '/staf/pemilik', 'canCreate'=>true,'canDelete'=>true,
            'fields' => [['name'=>'nama','label'=>'Nama pemilik','type'=>'text','required'=>true],['name'=>'no_wa','label'=>'Nomor WhatsApp','type'=>'tel','required'=>true]],
            'columns' => [['key'=>'nama','label'=>'Nama pemilik','type'=>''],['key'=>'no_wa','label'=>'WhatsApp','type'=>''],['key'=>'pets_count','label'=>'Jumlah hewan','type'=>'number']],
        ];
    @endphp
    @include('shared.master-crud')
</x-app-layout>
