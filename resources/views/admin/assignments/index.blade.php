@extends('layouts.app')

@section('title', 'Przydziały nauczania')

@section('content')
    <div class="page-head">
        <h1>Przydziały nauczania</h1>
        <a class="btn btn-primary" href="{{ route('admin.assignments.create') }}">Dodaj przydział</a>
    </div>

    <form class="card filters" method="get" action="{{ route('admin.assignments.index') }}">
        @include('partials.search-field', ['placeholder' => 'Klasa, przedmiot lub nauczyciel'])
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('admin.assignments.index') }}">Wyczyść</a>
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
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="{{ route('admin.assignments.edit', $assignment) }}">Edytuj</a>
                                <form method="post" action="{{ route('admin.assignments.destroy', $assignment) }}" data-confirm="Usunąć ten przydział?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="4">Nie znaleziono przydziałów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $assignments->links() }}
@endsection
