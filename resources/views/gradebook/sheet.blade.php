@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', $subject->name.', klasa '.$class->name)

@section('content')
    <p class="crumb"><a href="{{ route('gradebook.index') }}">← Lista arkuszy</a></p>

    <div class="page-head">
        <div>
            <h1>{{ $subject->name }}, klasa {{ $class->name }}</h1>
            <p class="muted">Średnia ważona klasy: <strong>{{ Averages::format($classAverage) }}</strong>. Kliknij ocenę, aby ją zmienić lub usunąć.</p>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="{{ route('grades.create', [$class, $subject]) }}">Dodaj ocenę</a>
            <a class="btn" href="{{ route('grades.bulk.create', [$class, $subject]) }}">Wpis zbiorczy</a>
        </div>
    </div>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Uczeń</th><th>Oceny</th><th class="num">Średnia</th><th></th></tr></thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="nowrap">{{ $student->last_name }} {{ $student->first_name }}</td>
                        <td>
                            <div class="grades">
                                @foreach ($student->grades as $grade)
                                    <x-grade-badge :grade="$grade" :href="route('grades.edit', $grade)" />
                                @endforeach
                            </div>
                        </td>
                        <td class="num"><strong>{{ Averages::format(Averages::weighted($student->grades)) }}</strong></td>
                        <td><a class="btn btn-sm" href="{{ route('grades.create', [$class, $subject, 'student' => $student->id]) }}">Dodaj</a></td>
                    </tr>
                @empty
                    <tr><td class="empty" colspan="4">W tej klasie nie ma uczniów.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
