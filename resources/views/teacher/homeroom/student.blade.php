@extends('layouts.app')

@section('title', $student->full_name)

@section('content')
    <p class="crumb"><a href="{{ route('teacher.homeroom.show', $class) }}">← Klasa {{ $class->name }}</a></p>

    <div class="page-head">
        <h1>{{ $student->full_name }}</h1>
        <a class="btn" href="{{ route('notes.create', ['student' => $student->id]) }}">Dodaj uwagę</a>
    </div>

    @include('partials.student-grades')

    <h2>Uwagi</h2>
    @include('partials.notes-list')
@endsection
