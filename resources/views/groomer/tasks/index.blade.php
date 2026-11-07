<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tugas Groomer</title>
</head>
<body>
    <h1>Halaman Tugas Groomer</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
