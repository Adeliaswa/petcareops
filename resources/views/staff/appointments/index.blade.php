<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Janji Temu Staff</title>
</head>
<body>
    <h1>Halaman Janji Temu Staff</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>