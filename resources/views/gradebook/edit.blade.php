@extends('layouts.app')

@section('title', 'Edycja oceny')

@section('content')
    <p class="crumb"><a href="{{ route('gradebook.sheet', [$class, $subject]) }}">← Arkusz ocen</a></p>

    <div class="page-head">
        <div>
            <h1>Edycja oceny</h1>
            <p class="muted">{{ $grade->student->full_name }}, {{ $subject->name }}, klasa {{ $class->name }}</p>
        </div>
    </div>

    <form class="card form" method="post" action="{{ route('grades.update', $grade) }}">
        @csrf
        @method('PUT')
        @include('partials.form-errors')
        @include('gradebook._fields')

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zapisz zmiany</button>
            <a class="btn" href="{{ route('gradebook.sheet', [$class, $subject]) }}">Anuluj</a>
        </div>
    </form>

    <form method="post" action="{{ route('grades.destroy', $grade) }}" data-confirm="Usunąć tę ocenę? Wpis pozostanie w historii.">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Usuń ocenę</button>
    </form>
@endsection
