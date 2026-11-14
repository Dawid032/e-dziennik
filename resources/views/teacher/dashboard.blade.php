@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', 'Pulpit nauczyciela')

@section('content')
    <div class="page-head">
        <h1>Pulpit nauczyciela</h1>
    </div>

    @if ($homeroomClasses->isNotEmpty())
        <section>
            <div class="page-head" style="margin-bottom: 10px"><h2>Wychowawstwo</h2></div>
            <div class="tiles">
                @foreach ($homeroomClasses as $class)
                    <a class="tile" href="{{ route('teacher.homeroom.show', $class) }}">
                        <strong>Klasa {{ $class->name }}</strong>
                        <span class="muted">{{ $class->school_year }}, uczniowie: {{ $class->students_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="page-head" style="margin-bottom: 10px"><h2>Moje przydziały</h2></div>
        <div class="tiles">
            @forelse ($assignments as $assignment)
                <a class="tile" href="{{ route('gradebook.sheet', [$assignment->class_id, $assignment->subject_id]) }}">
                    <strong>{{ $assignment->subject->name }}</strong>
                    <span>Klasa {{ $assignment->schoolClass->name }}, uczniowie: {{ $assignment->schoolClass->students_count }}</span><br>
                    <span class="muted">Średnia: {{ Averages::format($averages[$assignment->class_id.'-'.$assignment->subject_id] ?? null) }}</span>
                </a>
            @empty
                <div class="card empty">Nie masz jeszcze przydziałów.</div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="page-head" style="margin-bottom: 10px">
            <h2>Ostatnie wpisy</h2>
            <a class="btn btn-sm" href="{{ route('history.index') }}">Cała historia</a>
        </div>
        @include('partials.grade-history', ['history' => $history, 'showStudent' => true])
    </section>
@endsection
