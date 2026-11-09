
<x-app-layout>
    <x-slot name="header">Hewan</x-slot>

    @php
        $masterConfig = [
            'kind' => 'pet',
            'title' => 'Hewan',
            'singular' => 'Hewan',
            'description' => 'Kelola profil dan informasi kesehatan hewan.',
            'api' => '/staf/hewan',
            'canCreate' => true,
            'canDelete' => true,

            'fields' => [
                [
                    'name' => 'customer_id',
                    'label' => 'Pemilik',
                    'type' => 'select',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'name' => 'nama',
                    'label' => 'Nama hewan',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'spesies',
                    'label' => 'Jenis hewan',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        [
                            'value' => 'anjing',
                            'label' => 'Anjing',
                        ],
                        [
                            'value' => 'kucing',
                            'label' => 'Kucing',
                        ],
                    ],
                ],
                [
                    'name' => 'ras',
                    'label' => 'Ras',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'name' => 'ukuran',
                    'label' => 'Ukuran',
                    'type' => 'select',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'name' => 'jenis_bulu',
                    'label' => 'Jenis bulu',
                    'type' => 'select',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'name' => 'perilaku',
                    'label' => 'Perilaku',
                    'type' => 'select',
                    'required' => false,
                    'options' => [],
                ],
                [
                    'name' => 'alergi',
                    'label' => 'Alergi',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'name' => 'catatan_kesehatan',
                    'label' => 'Catatan kesehatan',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],

            'columns' => [
                [
                    'key' => 'nama',
                    'label' => 'Nama hewan',
                    'type' => '',
                ],
                [
                    'key' => 'spesies',
                    'label' => 'Jenis',
                    'type' => '',
                ],
                [
                    'key' => 'ras',
                    'label' => 'Ras',
                    'type' => '',
                ],
                [
                    'key' => 'customer.nama',
                    'label' => 'Pemilik',
                    'type' => '',
                ],
            ],
        ];
    @endphp

    @include('shared.master-crud')
</x-app-layout>
