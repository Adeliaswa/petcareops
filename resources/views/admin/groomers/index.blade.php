<x-app-layout>
    <x-slot name="header">Groomer</x-slot>

    @php
        $masterConfig = [
            'kind' => 'groomer',
            'title' => 'Groomer',
            'singular' => 'Groomer',
            'description' => 'Lihat profil groomer dan kelola jadwal kerjanya.',
            'api' => '/admin/groomer',
            'canCreate' => false,
            'canDelete' => false,

            // URL halaman detail groomer
            'detailBase' => '/admin/halaman/groomer/',
            'detailTimeOffBase' => '/admin/halaman/groomer/',

            'fields' => [
                [
                    'name' => 'level_skill',
                    'label' => 'Tingkat keahlian',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'bisa_kucing',
                    'label' => 'Bisa menangani kucing',
                    'type' => 'checkbox',
                    'required' => true,
                ],
                [
                    'name' => 'bisa_agresif',
                    'label' => 'Bisa menangani hewan agresif',
                    'type' => 'checkbox',
                    'required' => true,
                ],
            ],

            'columns' => [
                [
                    'key' => 'user.name',
                    'label' => 'Nama groomer',
                    'type' => '',
                ],
                [
                    'key' => 'user.email',
                    'label' => 'Email',
                    'type' => '',
                ],
                [
                    'key' => 'level_skill',
                    'label' => 'Keahlian',
                    'type' => '',
                ],
                [
                    'key' => 'user.is_active',
                    'label' => 'Status akun',
                    'type' => 'badge',
                ],
            ],
        ];
    @endphp

    @include('shared.master-crud')
</x-app-layout>