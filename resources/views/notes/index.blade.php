@extends('layouts.app')

@section('title', 'Uwagi')

@section('content')
    <div class="page-head">
        <h1>Uwagi</h1>
        @if (auth()->user()->isTeacher())
            <a class="btn btn-primary" href="{{ route('notes.create') }}">Dodaj wpis</a>
        @endif
    </div>

    <form class="card filters" method="get" action="{{ route('notes.index') }}">
        @include('partials.search-field', ['label' => 'Szukaj ucznia', 'placeholder' => 'Imię, nazwisko lub e-mail'])
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('notes.index') }}">Wyczyść</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Data</th><th>Uczeń</th><th>Rodzaj</th><th>Treść</th><th>Autor</th><th>Akcje</th></tr></thead>
            <tbody>
                @forelse ($notes as $note)
                    <tr>
                        <td class="nowrap">{{ $note->created_at->format('d.m.Y') }}</td>
                        <td class="nowrap">{{ $note->student->full_name }} ({{ $note->student->schoolClass?->name ?? '—' }})</td>
                        <td><span class="tag tag-{{ $note->type }}">{{ $note->type_name }}</span></td>
                        <td>{{ $note->content }}</td>
                        <td class="nowrap">{{ $note->teacher->full_name }}</td>
                        <td>
                            <div class="actions">
                                @if ($note->teacher_id === auth()->id())
                                    <a class="btn btn-sm" href="{{ route('notes.edit', $note) }}">Edytuj</a>
                                @endif
                                <form method="post" action="{{ route('notes.destroy', $note) }}" data-confirm="Usunąć ten wpis?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="6">Nie znaleziono wpisów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $notes->links() }}
@endsection
