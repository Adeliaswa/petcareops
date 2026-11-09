<x-app-layout>
    <x-slot name="header">Libur groomer</x-slot>

    @php
        $timeOffUrl = '/admin/groomer/' . $groomer->id . '/libur';

        $masterConfig = [
            'kind' => 'timeoff',
            'title' => 'Libur groomer',
            'singular' => 'Jadwal libur',
            'description' => 'Kelola jadwal libur groomer: ' . $groomer->user->name,
            'api' => $timeOffUrl,
            'baseUrl' => $timeOffUrl,
            'canCreate' => true,
            'canDelete' => true,

            'fields' => [
                [
                    'name' => 'tanggal_mulai',
                    'label' => 'Tanggal mulai',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'name' => 'tanggal_selesai',
                    'label' => 'Tanggal selesai',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'name' => 'alasan',
                    'label' => 'Alasan',
                    'type' => 'textarea',
                    'required' => false,
                    'wide' => true,
                ],
            ],

            'columns' => [
                [
                    'key' => 'tanggal_mulai',
                    'label' => 'Mulai',
                    'type' => 'date',
                ],
                [
                    'key' => 'tanggal_selesai',
                    'label' => 'Selesai',
                    'type' => 'date',
                ],
                [
                    'key' => 'alasan',
                    'label' => 'Alasan',
                    'type' => '',
                ],
            ],
        ];
    @endphp

    @include('shared.master-crud')
</x-app-layout>