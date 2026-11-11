<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NUARTE - IFRN/MO')</title>
    <link rel="icon" href="{{ asset('images/minilogo.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        body { background: #f7f8fb; color: #20242a; }
        .navbar-brand img { width: 150px; height: auto; }
        .app-card { border: 0; border-radius: 1rem; box-shadow: 0 0.5rem 2rem rgba(31, 38, 135, .08); }
        .event-color { width: .8rem; height: .8rem; border-radius: 50%; display: inline-block; }
    </style>
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="NUARTE">
        </a>
        <div class="d-flex align-items-center gap-3">
            <a class="nav-link" href="{{ route('events.public') }}">Agenda</a>
            @auth
                <a class="nav-link" href="{{ route('admin.events.index') }}">Administrar</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Sair</button>
                </form>
            @else
                <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Entrar</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container pb-5">
    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
