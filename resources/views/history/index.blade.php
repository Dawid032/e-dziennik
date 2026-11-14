@extends('layouts.app')

@section('title', 'Historia zmian ocen')

@section('content')
    <div class="page-head">
        <h1>Historia zmian ocen</h1>
    </div>

    <form class="card filters" method="get" action="{{ route('history.index') }}">
        <x-select name="subject" label="Przedmiot" :options="$subjects" :value="request('subject')" placeholder="Wszystkie przedmioty" />
        <x-select name="action" label="Akcja" :options="$actions" :value="request('action')" placeholder="Wszystkie akcje" />
        <div class="actions">
            <button class="btn btn-primary" type="submit">Filtruj</button>
            <a class="btn" href="{{ route('history.index') }}">Wyczyść</a>
        </div>
    </form>

    @include('partials.grade-history', ['showStudent' => true])

    {{ $history->links() }}
@endsection
