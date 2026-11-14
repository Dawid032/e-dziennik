@extends('layouts.app')

@section('title', 'Wpis zbiorczy')

@section('content')
    <p class="crumb"><a href="{{ route('gradebook.sheet', [$class, $subject]) }}">← Arkusz ocen</a></p>

    <div class="page-head">
        <div>
            <h1>Wpis zbiorczy: {{ $subject->name }}, klasa {{ $class->name }}</h1>
            <p class="muted">Pozostaw puste pole przy uczniu, który nie otrzymuje oceny.</p>
        </div>
    </div>

    <form class="form" method="post" action="{{ route('grades.bulk.store', [$class, $subject]) }}">
        @csrf
        @include('partials.form-errors')

        <div class="card form-grid">
            <x-select name="category" label="Kategoria" :options="array_combine(config('dziennik.grade_categories'), config('dziennik.grade_categories'))"
                placeholder="Wybierz kategorię" required />
            <x-input name="weight" label="Waga (1–5)" type="number" value="1" required min="1" max="5" step="1" />
            <div class="wide">
                <x-input name="description" label="Opis" maxlength="255" placeholder="np. Sprawdzian z funkcji" />
            </div>
        </div>

        <div class="card table-wrap">
            <table>
                <thead><tr><th>Uczeń</th><th class="num">Ocena (1–6)</th></tr></thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td><label for="v-{{ $student->id }}">{{ $student->last_name }} {{ $student->first_name }}</label></td>
                            <td class="num">
                                <input id="v-{{ $student->id }}" @class(['score', 'invalid' => $errors->has('values.'.$student->id)])
                                    type="number" name="values[{{ $student->id }}]" min="1" max="6" step="1" value="{{ old('values.'.$student->id) }}">
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="2">W tej klasie nie ma uczniów.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zapisz oceny</button>
            <a class="btn" href="{{ route('gradebook.sheet', [$class, $subject]) }}">Anuluj</a>
        </div>
    </form>
@endsection
