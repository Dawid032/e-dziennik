@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', 'Wychowawstwo '.$class->name)

@section('content')
    <div class="page-head">
        <div>
            <h1>Klasa {{ $class->name }}</h1>
            <p class="muted">Panel wychowawcy, {{ $class->school_year }}. Widok tylko do odczytu.</p>
        </div>
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Uczeń</th>
                    @foreach ($subjects as $subject)
                        <th class="num">{{ $subject->name }}</th>
                    @endforeach
                    <th class="num">Ogólna</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="nowrap"><a href="{{ route('teacher.homeroom.student', [$class, $student]) }}">{{ $student->last_name }} {{ $student->first_name }}</a></td>
                        @foreach ($subjects as $subject)
                            @php($average = Averages::weighted($student->grades->where('subject_id', $subject->id)))
                            <td class="num">{{ $average === null ? '—' : Averages::format($average) }}</td>
                        @endforeach
                        <td class="num"><strong>{{ Averages::format(Averages::weighted($student->grades)) }}</strong></td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="{{ $subjects->count() + 2 }}">W tej klasie nie ma uczniów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
