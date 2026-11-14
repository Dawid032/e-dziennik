@extends('layouts.center')

@section('title', 'Logowanie')

@section('content')
    <h1>Logowanie</h1>

    <form class="form" method="post" action="{{ route('login.store') }}">
        @csrf
        <x-input name="email" label="E-mail" type="email" required autofocus autocomplete="username" maxlength="150" />
        <x-input name="password" label="Hasło" type="password" required autocomplete="current-password" />
        <button class="btn btn-primary" type="submit">Zaloguj się</button>
    </form>

    <p class="center-note">Konto zakłada administrator szkoły. Jeśli nie pamiętasz hasła, skontaktuj się z sekretariatem.</p>
@endsection
