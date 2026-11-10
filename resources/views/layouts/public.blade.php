<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="public-site">

    <a href="#main-content" class="public-skip-link">
        Lewati ke konten utama
    </a>

    @yield('content')

</body>
</html>