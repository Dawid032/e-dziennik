<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    /** Nauczyciel widzi uwagi, które sam wystawił, administrator wszystkie. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notes = Note::with(['student.schoolClass', 'teacher'])
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->whereHas('student', fn ($student) => $student->search($request->query('q')));
            })
            ->latest('id')
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('notes.index', ['notes' => $notes]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isTeacher(), 403);

        $students = User::ofRole(User::ROLE_STUDENT)
            ->with('schoolClass')
            ->whereIn('class_id', $request->user()->relatedClassIds())
            ->alphabetical()
            ->get()
            ->mapWithKeys(fn ($student) => [$student->id => $student->full_name.' ('.$student->schoolClass->name.')']);

        return view('notes.create', [
            'note' => new Note,
            'students' => $students,
            'types' => Note::TYPES,
            'selectedStudent' => $request->integer('student') ?: null,
        ]);
    }

    public function store(NoteRequest $request): RedirectResponse
    {
        Note::create($request->validated() + ['teacher_id' => $request->user()->id]);

        return redirect()->route('notes.index')->with('success', 'Wpis został dodany.');
    }

    public function edit(Request $request, Note $note): View
    {
        abort_unless($note->teacher_id === $request->user()->id, 403);

        return view('notes.edit', ['note' => $note->load('student'), 'types' => Note::TYPES]);
    }

    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $note->update($request->validated());

        return redirect()->route('notes.index')->with('success', 'Zmiany zostały zapisane.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $note->teacher_id === $user->id, 403);

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Wpis został usunięty.');
    }
}
