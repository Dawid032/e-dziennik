@extends('layouts.app')

@section('title', 'Zmiana hasła')

@section('content')
    <div class="page-head">
        <h1>Zmiana hasła</h1>
    </div>

    <form class="card form" method="post" action="{{ route('account.password.update') }}">
        @csrf
        @method('PUT')
        @include('partials.form-errors')

        <div class="form-grid">
            <div class="wide">
                <x-input name="current_password" label="Obecne hasło" type="password" required autocomplete="current-password" />
            </div>
            <x-input name="password" label="Nowe hasło" type="password" required autocomplete="new-password" maxlength="72"
                :pattern="\App\Support\ValidationPatterns::PASSWORD" title="Co najmniej 8 znaków, w tym litera i cyfra"
                help="Co najmniej 8 znaków, w tym litera i cyfra." />
            <x-input name="password_confirmation" label="Powtórz nowe hasło" type="password" required autocomplete="new-password" />
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zmień hasło</button>
        </div>
    </form>
@endsection
