@php
    $user = auth()->user();

    // Każda pozycja: [etykieta, adres, wzorce nazw tras podświetlające pozycję].
    $links = match ($user->role) {
        'admin' => [
            ['Pulpit', route('admin.dashboard'), ['admin.dashboard']],
            ['Użytkownicy', route('admin.users.index'), ['admin.users.*']],
            ['Klasy', route('admin.classes.index'), ['admin.classes.*']],
            ['Przedmioty', route('admin.subjects.index'), ['admin.subjects.*']],
            ['Przydziały', route('admin.assignments.index'), ['admin.assignments.*']],
            ['Oceny', route('gradebook.index'), ['gradebook.*', 'grades.*']],
            ['Historia zmian', route('history.index'), ['history.*']],
            ['Uwagi', route('notes.index'), ['notes.*']],
        ],
        'teacher' => [
            ['Pulpit', route('teacher.dashboard'), ['teacher.dashboard']],
            ['Oceny', route('gradebook.index'), ['gradebook.*', 'grades.*']],
            ['Historia zmian', route('history.index'), ['history.*']],
            ['Uwagi', route('notes.index'), ['notes.*']],
        ],
        'student' => [
            ['Pulpit', route('student.dashboard'), ['student.dashboard']],
            ['Moje oceny', route('student.grades'), ['student.grades']],
            ['Historia zmian', route('student.history'), ['student.history']],
            ['Uwagi', route('student.notes'), ['student.notes']],
        ],
        'parent' => [
            ['Moje dzieci', route('parent.dashboard'), ['parent.*']],
        ],
    };

    if ($user->isTeacher()) {
        foreach ($user->homeroomClasses as $class) {
            $links[] = ['Wychowawstwo '.$class->name, route('teacher.homeroom.show', $class), ['teacher.homeroom.*']];
        }
    }
@endphp

@foreach ($links as [$label, $url, $patterns])
    <a href="{{ $url }}" @class(['active' => request()->routeIs(...$patterns)])>{{ $label }}</a>
@endforeach
