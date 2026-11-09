{{-- Logo resmi Pet Care Ops. Gunakan di sidebar, header, dan halaman publik. --}}
@props(['size' => 'sidebar'])
<img
    src="{{ asset('images/petcare-logo.svg') }}"
    alt="Pet Care Ops"
    {{ $attributes->class(['brand-logo', 'brand-logo--' . $size]) }}
    width="209"
    height="119"
    decoding="async"
>
