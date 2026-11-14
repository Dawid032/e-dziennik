@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', 'Pulpit ucznia')

@section('content')
    <div class="page-head">
        <h1>Witaj, {{ $student->first_name }}</h1>
    </div>

    <div class="stat-grid">
        <div class="stat"><span class="stat-label">Klasa</span><span class="stat-value">{{ $student->schoolClass?->name ?? '—' }}</span></div>
        <div class="stat"><span class="stat-label">Wychowawca</span><strong>{{ $student->schoolClass?->homeroomTeacher?->full_name ?? '—' }}</strong></div>
        <div class="stat"><span class="stat-label">Średnia ważona</span><span class="stat-value">{{ Averages::format($overall) }}</span></div>
    </div>

    <div class="grid-2">
        <section class="card">
            <h2>Ostatnie oceny</h2>
            <ul class="mini-list">
                @forelse ($latestGrades as $grade)
                    <li>
                        <x-grade-badge :grade="$grade" />
                        <span>{{ $grade->subject->name }} <small>{{ $grade->category }}, {{ $grade->created_at->format('d.m.Y') }}</small></span>
                    </li>
                @empty
                    <li class="muted">Nie ma jeszcze ocen.</li>
                @endforelse
            </ul>
            <p style="margin-top: 10px"><a href="{{ route('student.grades') }}">Wszystkie oceny</a></p>
        </section>

        <section>
            <div class="page-head" style="margin-bottom: 10px"><h2>Ostatnie uwagi</h2></div>
            @include('partials.notes-list', ['notes' => $latestNotes])
            <p style="margin-top: 10px"><a href="{{ route('student.notes') }}">Wszystkie uwagi</a></p>
        </section>
    </div>
@endsection
