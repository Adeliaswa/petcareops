<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    @auth
        @if (auth()->user()->hasRole('groomer'))
            @include('layouts.partials.groomer-shell')
        @else
            @include('layouts.partials.staff-shell')
        @endif
    @else
        <main>{{ $slot }}</main>
    @endauth
</body>
</html>
