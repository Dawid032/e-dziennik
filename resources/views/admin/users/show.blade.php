@extends('layouts.app')

@section('title', $user->full_name)

@section('content')
    <p class="crumb"><a href="{{ route('admin.users.index') }}">← Użytkownicy</a></p>

    <div class="page-head">
        <h1>{{ $user->full_name }}</h1>
        <div class="actions">
            <a class="btn" href="{{ route('admin.users.edit', $user) }}">Edytuj</a>
            <form method="post" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Usunąć konto: {{ $user->full_name }}?">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Usuń</button>
            </form>
        </div>
    </div>

    <div class="card">
        <dl class="details-list">
            <dt>E-mail</dt><dd>{{ $user->email }}</dd>
            <dt>Rola</dt><dd>{{ $user->role_name }}</dd>
            <dt>Konto utworzone</dt><dd>{{ $user->created_at->format('d.m.Y') }}</dd>

            @if ($user->isStudent())
                <dt>Klasa</dt><dd>{{ $user->schoolClass?->label ?? '—' }}</dd>
                <dt>Liczba ocen</dt><dd>{{ $user->grades_count }}</dd>
                <dt>Rodzice</dt>
                <dd>
                    @forelse ($user->parents as $parent)
                        <a href="{{ route('admin.users.show', $parent) }}">{{ $parent->full_name }}</a>@if (! $loop->last), @endif
                    @empty
                        —
                    @endforelse
                </dd>
            @endif

            @if ($user->isParent())
                <dt>Dzieci</dt>
                <dd>
                    @forelse ($user->children as $child)
                        <a href="{{ route('admin.users.show', $child) }}">{{ $child->full_name }}</a> ({{ $child->schoolClass?->name ?? 'bez klasy' }})@if (! $loop->last), @endif
                    @empty
                        —
                    @endforelse
                </dd>
            @endif
        </dl>
    </div>
@endsection
