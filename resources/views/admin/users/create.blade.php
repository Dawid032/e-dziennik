@extends('layouts.app')

@section('title', 'Rejestracja użytkownika')

@section('content')
    <div class="page-head">
        <div>
            <h1>Rejestracja użytkownika</h1>
            <p class="muted">Konta zakłada wyłącznie administrator, także kolejnych administratorów.</p>
        </div>
    </div>

    <form class="card form" method="post" action="{{ route('admin.users.store') }}">
        @csrf
        @include('admin.users._form', ['submitLabel' => 'Utwórz konto'])
    </form>
@endsection
