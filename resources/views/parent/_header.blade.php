<p class="crumb"><a href="{{ route('parent.dashboard') }}">← Moje dzieci</a></p>

<div class="page-head">
    <div>
        <h1>{{ $student->full_name }}</h1>
        <p class="muted">Klasa {{ $student->schoolClass?->name ?? '—' }}, wychowawca: {{ $student->schoolClass?->homeroomTeacher?->full_name ?? '—' }}</p>
    </div>
</div>

<nav class="tabs" aria-label="Dane dziecka">
    <a href="{{ route('parent.children.grades', $student) }}" @class(['active' => request()->routeIs('parent.children.grades')])>Oceny</a>
    <a href="{{ route('parent.children.history', $student) }}" @class(['active' => request()->routeIs('parent.children.history')])>Historia zmian</a>
    <a href="{{ route('parent.children.notes', $student) }}" @class(['active' => request()->routeIs('parent.children.notes')])>Uwagi</a>
</nav>
