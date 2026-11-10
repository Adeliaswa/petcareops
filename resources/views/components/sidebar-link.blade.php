@props(['href', 'icon', 'label', 'active' => false])
<a href="{{ $href }}" {{ $attributes->class(['nav__link', 'is-active' => $active]) }} @if($active) aria-current="page" @endif>
    <x-icon :name="$icon" />
    <span>{{ $label }}</span>
</a>
