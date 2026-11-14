@extends('layouts.app')

@section('title', 'Klasy')

@section('content')
    <div class="page-head">
        <h1>Klasy</h1>
        <a class="btn btn-primary" href="{{ route('admin.classes.create') }}">Dodaj klasę</a>
    </div>

    <form class="card filters" method="get" action="{{ route('admin.classes.index') }}">
        @include('partials.search-field', ['placeholder' => 'Nazwa lub rok szkolny'])
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('admin.classes.index') }}">Wyczyść</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Klasa</th><th>Rok szkolny</th><th>Wychowawca</th><th class="num">Uczniowie</th><th>Akcje</th></tr></thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr>
                        <td><strong>{{ $class->name }}</strong></td>
                        <td>{{ $class->school_year }}</td>
                        <td>{{ $class->homeroomTeacher?->full_name ?? '—' }}</td>
                        <td class="num">{{ $class->students_count }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="{{ route('admin.classes.edit', $class) }}">Edytuj</a>
                                <form method="post" action="{{ route('admin.classes.destroy', $class) }}" data-confirm="Usunąć klasę {{ $class->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="5">Nie znaleziono klas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $classes->links() }}
@endsection
