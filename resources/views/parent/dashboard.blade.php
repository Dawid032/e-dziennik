@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', 'Moje dzieci')

@section('content')
    <div class="page-head"><h1>Moje dzieci</h1></div>

    <div class="tiles">
        @forelse ($children as $child)
            <a class="tile" href="{{ route('parent.children.grades', $child) }}">
                <strong>{{ $child->full_name }}</strong>
                <span>Klasa {{ $child->schoolClass?->name ?? '—' }}</span><br>
                <span class="muted">Średnia ważona: {{ Averages::format(Averages::weighted($child->grades)) }}</span>
            </a>
        @empty
            <div class="card empty">Do tego konta nie przypisano jeszcze żadnego dziecka.</div>
        @endforelse
    </div>
@endsection
