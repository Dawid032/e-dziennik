<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · E-Dziennik</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main class="center-page">
        <div class="center-card">
            <p class="error-code">@yield('code')</p>
            <h1>@yield('heading')</h1>
            <p class="muted">@yield('message')</p>
            <p class="center-note"><a href="{{ url('/') }}">Wróć do strony głównej</a></p>
        </div>
    </main>
</body>
</html>
