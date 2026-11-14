@extends('layouts.app')

@section('title', 'Oceny')

@section('content')
    <div class="page-head">
        <h1>Oceny</h1>
    </div>

    @if (auth()->user()->isAdmin())
        <form class="card filters" method="get" action="{{ route('gradebook.open') }}">
            <x-select name="class_id" label="Klasa" :options="$classes" placeholder="Wybierz klasę" required />
            <x-select name="subject_id" label="Przedmiot" :options="$subjects" placeholder="Wybierz przedmiot" required />
            <div class="actions">
                <button class="btn btn-primary" type="submit">Otwórz arkusz</button>
            </div>
        </form>
    @endif

    <form class="card filters" method="get" action="{{ route('gradebook.index') }}">
        @include('partials.search-field', ['placeholder' => 'Klasa, przedmiot lub nauczyciel'])
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('gradebook.index') }}">Wyczyść</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Klasa</th><th>Przedmiot</th><th>Nauczyciel</th><th>Akcje</th></tr></thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td><strong>{{ $assignment->schoolClass->name }}</strong></td>
                        <td>{{ $assignment->subject->name }}</td>
                        <td>{{ $assignment->teacher->full_name }}</td>
                        <td><a class="btn btn-sm" href="{{ route('gradebook.sheet', [$assignment->class_id, $assignment->subject_id]) }}">Arkusz ocen</a></td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="4">Nie znaleziono przydziałów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $assignments->links() }}
@endsection
