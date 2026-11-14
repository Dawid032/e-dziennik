<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · E-Dziennik</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">E</span>E-Dziennik</a>
        <div class="topbar-user">
            <span>{{ auth()->user()->full_name }}</span>
            <span class="role-tag">{{ auth()->user()->role_name }}</span>
            <a class="topbar-link" href="{{ route('account.password') }}">Zmień hasło</a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="topbar-link" type="submit">Wyloguj</button>
            </form>
        </div>
    </header>

    <div class="shell">
        <nav class="sidenav" aria-label="Menu główne">
            @include('partials.nav')
        </nav>

        <main class="content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>
