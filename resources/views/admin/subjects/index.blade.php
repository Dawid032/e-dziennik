@extends('layouts.app')

@section('title', 'Przedmioty')

@section('content')
    <div class="page-head">
        <h1>Przedmioty</h1>
        <a class="btn btn-primary" href="{{ route('admin.subjects.create') }}">Dodaj przedmiot</a>
    </div>

    <form class="card filters" method="get" action="{{ route('admin.subjects.index') }}">
        @include('partials.search-field', ['placeholder' => 'Nazwa przedmiotu'])
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('admin.subjects.index') }}">Wyczyść</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Przedmiot</th><th class="num">Przydziały</th><th>Akcje</th></tr></thead>
            <tbody>
                @forelse ($subjects as $subject)
                    <tr>
                        <td><strong>{{ $subject->name }}</strong></td>
                        <td class="num">{{ $subject->assignments_count }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="{{ route('admin.subjects.edit', $subject) }}">Edytuj</a>
                                <form method="post" action="{{ route('admin.subjects.destroy', $subject) }}" data-confirm="Usunąć przedmiot: {{ $subject->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="3">Nie znaleziono przedmiotów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subjects->links() }}
@endsection
