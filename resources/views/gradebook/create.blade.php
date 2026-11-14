@extends('layouts.app')

@section('title', 'Nowa ocena')

@section('content')
    <p class="crumb"><a href="{{ route('gradebook.sheet', [$class, $subject]) }}">← Arkusz ocen</a></p>

    <div class="page-head">
        <h1>Nowa ocena: {{ $subject->name }}, klasa {{ $class->name }}</h1>
    </div>

    <form class="card form" method="post" action="{{ route('grades.store', [$class, $subject]) }}">
        @csrf
        @include('partials.form-errors')

        <x-select name="student_id" label="Uczeń" :options="$students" :value="$selectedStudent" placeholder="Wybierz ucznia" required />
        @include('gradebook._fields')

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zapisz ocenę</button>
            <a class="btn" href="{{ route('gradebook.sheet', [$class, $subject]) }}">Anuluj</a>
        </div>
    </form>
@endsection
