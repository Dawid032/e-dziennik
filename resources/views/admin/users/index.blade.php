@extends('layouts.app')

@section('title', 'Użytkownicy')

@section('content')
    <div class="page-head">
        <h1>Użytkownicy</h1>
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Rejestracja użytkownika</a>
    </div>

    <form class="card filters" method="get" action="{{ route('admin.users.index') }}">
        @include('partials.search-field', ['placeholder' => 'Imię, nazwisko lub e-mail'])
        <x-select name="role" label="Rola" :options="$roles" :value="request('role')" placeholder="Wszystkie role" />
        <x-select name="class" label="Klasa" :options="$classes" :value="request('class')" placeholder="Wszystkie klasy" />
        <div class="actions">
            <button class="btn btn-primary" type="submit">Szukaj</button>
            <a class="btn" href="{{ route('admin.users.index') }}">Wyczyść</a>
        </div>
    </form>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Nazwisko i imię</th><th>E-mail</th><th>Rola</th><th>Klasa</th><th>Akcje</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->last_name }} {{ $user->first_name }}</a></td>
                        <td>{{ $user->email }}</td>
                        <td><span class="tag">{{ $user->role_name }}</span></td>
                        <td>{{ $user->schoolClass?->name ?? '—' }}</td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="{{ route('admin.users.edit', $user) }}">Edytuj</a>
                                <form method="post" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Usunąć konto: {{ $user->full_name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="5">Nie znaleziono użytkowników.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
@endsection
