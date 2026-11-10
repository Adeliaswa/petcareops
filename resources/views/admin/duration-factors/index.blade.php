<x-app-layout>
    <x-slot name="header">Faktor Durasi</x-slot>

    @php
        $masterConfig = [
            'kind' => 'factor',
            'title' => 'Faktor Durasi',
            'singular' => 'Faktor Durasi',
            'description' => 'Atur faktor yang memengaruhi estimasi waktu grooming.',
            'api' => '/admin/faktor-durasi',
            'canCreate' => true,
            'canDelete' => true,

            'fields' => [
                [
                    'name' => 'kategori',
                    'label' => 'Kategori',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        [
                            'value' => 'ukuran',
                            'label' => 'Ukuran',
                        ],
                        [
                            'value' => 'jenis_bulu',
                            'label' => 'Jenis bulu',
                        ],
                        [
                            'value' => 'perilaku',
                            'label' => 'Perilaku',
                        ],
                    ],
                ],
                [
                    'name' => 'key',
                    'label' => 'Key',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'label',
                    'label' => 'Label',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'multiplier',
                    'label' => 'Multiplier',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'name' => 'extra_minutes',
                    'label' => 'Tambahan (menit)',
                    'type' => 'number',
                    'required' => true,
                ],
            ],

            'columns' => [
                [
                    'key' => 'kategori',
                    'label' => 'Kategori',
                    'type' => '',
                ],
                [
                    'key' => 'key',
                    'label' => 'Key',
                    'type' => '',
                ],
                [
                    'key' => 'label',
                    'label' => 'Label',
                    'type' => '',
                ],
                [
                    'key' => 'multiplier',
                    'label' => 'Multiplier',
                    'type' => 'number',
                ],
                [
                    'key' => 'extra_minutes',
                    'label' => 'Tambahan (menit)',
                    'type' => 'number',
                ],
            ],
        ];
    @endphp

    @include('shared.master-crud')

</x-app-layout>