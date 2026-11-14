@use('App\Services\GradeAverageCalculator', 'Averages')

{{-- Wspólny widok ocen ucznia: panel ucznia, rodzica i wychowawcy. Oczekuje $subjects i $overall. --}}
<div class="stat-grid">
    <div class="stat">
        <span class="stat-label">Średnia ważona ogólna</span>
        <span class="stat-value">{{ Averages::format($overall) }}</span>
    </div>
</div>

@forelse ($subjects as $row)
    <section class="card">
        <div class="subject-head">
            <h2>{{ $row->subject->name }}</h2>
            <span>Średnia ważona: <strong>{{ Averages::format($row->average) }}</strong></span>
        </div>

        <div class="grades">
            @foreach ($row->grades as $grade)
                <x-grade-badge :grade="$grade" />
            @endforeach
        </div>

        <details>
            <summary>Szczegóły ocen</summary>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Data</th><th>Ocena</th><th>Kategoria</th><th>Waga</th><th>Opis</th><th>Wystawił(a)</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($row->grades as $grade)
                            <tr>
                                <td class="nowrap">{{ $grade->created_at->format('d.m.Y') }}</td>
                                <td><x-grade-badge :grade="$grade" /></td>
                                <td>{{ $grade->category }}</td>
                                <td>{{ $grade->weight }}</td>
                                <td>{{ $grade->description ?? '—' }}</td>
                                <td>{{ $grade->teacher->full_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </section>
@empty
    <div class="card empty">Nie ma jeszcze żadnych ocen.</div>
@endforelse
