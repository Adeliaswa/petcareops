<x-app-layout>
    <x-slot name="header">Jadwal libur groomer</x-slot>
    @php
        $masterConfig = [
            'kind' => 'alltimeoff', 'title' => 'Jadwal libur groomer', 'singular' => 'Jadwal libur groomer',
            'description' => 'Ringkasan seluruh jadwal libur groomer.', 'api' => '/admin/libur', 'canCreate'=>false,'canDelete'=>false,
            'fields' => [],
            'columns' => [['key'=>'groomer.user.name','label'=>'Groomer','type'=>''],['key'=>'tanggal_mulai','label'=>'Mulai','type'=>'date'],['key'=>'tanggal_selesai','label'=>'Selesai','type'=>'date'],['key'=>'alasan','label'=>'Alasan','type'=>'']],
        ];
    @endphp
    @include('shared.master-crud')
</x-app-layout>
