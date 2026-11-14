@extends('layouts.app')

@section('title', 'Edycja wpisu')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edycja wpisu</h1>
            <p class="muted">Uczeń: {{ $note->student->full_name }}</p>
        </div>
    </div>

    <form class="card form" method="post" action="{{ route('notes.update', $note) }}">
        @csrf
        @method('PUT')
        @include('partials.form-errors')

        <x-select name="type" label="Rodzaj" :options="$types" :value="$note->type" placeholder="Wybierz rodzaj" required />
        <x-textarea name="content" label="Treść" :value="$note->content" required maxlength="1000" />

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zapisz zmiany</button>
            <a class="btn" href="{{ route('notes.index') }}">Anuluj</a>
        </div>
    </form>
@endsection
