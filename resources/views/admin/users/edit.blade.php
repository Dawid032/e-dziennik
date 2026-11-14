@extends('layouts.app')

@section('title', 'Edycja użytkownika')

@section('content')
    <div class="page-head">
        <h1>Edycja: {{ $user->full_name }}</h1>
    </div>

    <form class="card form" method="post" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['submitLabel' => 'Zapisz zmiany'])
    </form>
@endsection
