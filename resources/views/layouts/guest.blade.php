<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head', ['title' => 'Login'])
</head>
<body class="auth-body">
    <a href="#login-form" class="skip-link">Lewati ke formulir login</a>
    <main class="auth-card">
        @include('layouts.partials.auth-hero')
        <section class="auth-panel" aria-labelledby="login-title">
            <svg class="auth-paw auth-paw--1" aria-hidden="true"><use href="#paw"/></svg>
            <svg class="auth-paw auth-paw--2" aria-hidden="true"><use href="#paw"/></svg>
            {{ $slot }}
        </section>
    </main>
</body>
</html>
