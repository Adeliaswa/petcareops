<x-app-layout>
    <x-slot name="header">Shift groomer</x-slot>

    @php
        $shiftUrl = '/admin/groomer/' . $groomer->id . '/shift';

        $masterConfig = [
            'kind' => 'shift',
            'title' => 'Shift groomer',
            'singular' => 'Shift groomer',
            'description' => 'Kelola hari dan jam kerja groomer: ' . $groomer->user->name,
            'api' => $shiftUrl,
            'baseUrl' => $shiftUrl,
            'canCreate' => true,
            'canDelete' => true,

            'fields' => [
                [
                    'name' => 'hari',
                    'label' => 'Hari',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 1, 'label' => 'Senin'],
                        ['value' => 2, 'label' => 'Selasa'],
                        ['value' => 3, 'label' => 'Rabu'],
                        ['value' => 4, 'label' => 'Kamis'],
                        ['value' => 5, 'label' => 'Jumat'],
                        ['value' => 6, 'label' => 'Sabtu'],
                        ['value' => 7, 'label' => 'Minggu'],
                    ],
                ],
                [
                    'name' => 'jam_mulai',
                    'label' => 'Jam mulai',
                    'type' => 'time',
                    'required' => true,
                ],
                [
                    'name' => 'jam_selesai',
                    'label' => 'Jam selesai',
                    'type' => 'time',
                    'required' => true,
                ],
            ],

            'columns' => [
                ['key' => 'hari', 'label' => 'Hari', 'type' => 'day'],
                ['key' => 'jam_mulai', 'label' => 'Mulai', 'type' => ''],
                ['key' => 'jam_selesai', 'label' => 'Selesai', 'type' => ''],
            ],
        ];
    @endphp

    @include('shared.master-crud')
</x-app-layout>