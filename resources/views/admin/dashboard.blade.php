@extends('layouts.app')

@use('App\Services\GradeAverageCalculator', 'Averages')

@section('title', 'Pulpit administratora')

@section('content')
    <div class="page-head">
        <h1>Pulpit administratora</h1>
    </div>

    <div class="stat-grid">
        @foreach (\App\Models\User::ROLES as $role => $label)
            <div class="stat">
                <span class="stat-label">{{ $label }}</span>
                <span class="stat-value">{{ $roleCounts[$role] ?? 0 }}</span>
            </div>
        @endforeach
        <div class="stat"><span class="stat-label">Klasy</span><span class="stat-value">{{ $classCount }}</span></div>
        <div class="stat"><span class="stat-label">Przedmioty</span><span class="stat-value">{{ $subjectCount }}</span></div>
        <div class="stat"><span class="stat-label">Oceny</span><span class="stat-value">{{ $gradeCount }}</span></div>
    </div>

    <section>
        <div class="page-head" style="margin-bottom: 10px"><h2>Średnia ważona klas</h2></div>
        <div class="card table-wrap">
            <table>
                <thead><tr><th>Klasa</th><th>Rok szkolny</th><th>Wychowawca</th><th class="num">Uczniowie</th><th class="num">Średnia</th></tr></thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td><strong>{{ $class->name }}</strong></td>
                            <td>{{ $class->school_year }}</td>
                            <td>{{ $class->homeroomTeacher?->full_name ?? '—' }}</td>
                            <td class="num">{{ $class->students_count }}</td>
                            <td class="num"><strong>{{ Averages::format($averages[$class->id] ?? null) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Nie ma jeszcze żadnych klas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="page-head" style="margin-bottom: 10px">
            <h2>Ostatnie zmiany ocen</h2>
            <a class="btn btn-sm" href="{{ route('history.index') }}">Cała historia</a>
        </div>
        @include('partials.grade-history', ['history' => $history, 'showStudent' => true])
    </section>
@endsection
