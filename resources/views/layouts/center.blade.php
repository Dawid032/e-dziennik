<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · E-Dziennik</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main class="center-page">
        <div class="center-card">
            <span class="brand"><span class="brand-mark" aria-hidden="true">E</span>E-Dziennik</span>
            @yield('content')
        </div>
    </main>
</body>
</html>
