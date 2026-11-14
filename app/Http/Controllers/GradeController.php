<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
use App\Http\Requests\OpenGradebookRequest;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Services\GradeAverageCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    /** Lista arkuszy: nauczyciel widzi swoje przydziały, administrator wszystkie. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $assignments = TeachingAssignment::with(['schoolClass', 'subject', 'teacher'])
            ->when($user->isTeacher(), fn ($query) => $query->where('teacher_id', $user->id))
            ->search($request->query('q'))
            ->ordered()
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('gradebook.index', [
            'assignments' => $assignments,
            'classes' => SchoolClass::orderBy('name')->get()->pluck('label', 'id'),
            'subjects' => Subject::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /** Formularz administratora: wybór dowolnej klasy i przedmiotu. */
    public function open(OpenGradebookRequest $request): RedirectResponse
    {
        return redirect()->route('gradebook.sheet', [
            'class' => $request->validated('class_id'),
            'subject' => $request->validated('subject_id'),
        ]);
    }

    public function sheet(Request $request, SchoolClass $class, Subject $subject): View
    {
        abort_unless($request->user()->canGrade($class->id, $subject->id), 403);

        $students = $class->students()
            ->with(['grades' => fn ($query) => $query->where('subject_id', $subject->id)->oldest()])
            ->get();

        return view('gradebook.sheet', [
            'class' => $class,
            'subject' => $subject,
            'students' => $students,
            'classAverage' => GradeAverageCalculator::weighted($students->flatMap->grades),
        ]);
    }

    public function create(Request $request, SchoolClass $class, Subject $subject): View
    {
        abort_unless($request->user()->canGrade($class->id, $subject->id), 403);

        return view('gradebook.create', [
            'class' => $class,
            'subject' => $subject,
            'grade' => new Grade(['weight' => 1]),
            'students' => $class->students()->get()->pluck('full_name', 'id'),
            'selectedStudent' => $request->integer('student') ?: null,
        ]);
    }

    public function store(GradeRequest $request, SchoolClass $class, Subject $subject): RedirectResponse
    {
        Grade::create($request->validated() + [
            'subject_id' => $subject->id,
            'teacher_id' => $request->user()->id,
        ]);

        return redirect()->route('gradebook.sheet', [$class, $subject])->with('success', 'Ocena została dodana.');
    }

    public function edit(Request $request, Grade $grade): View
    {
        $grade->load(['student.schoolClass', 'subject']);
        abort_unless($request->user()->canGrade($grade->student->class_id, $grade->subject_id), 403);

        return view('gradebook.edit', [
            'grade' => $grade,
            'class' => $grade->student->schoolClass,
            'subject' => $grade->subject,
        ]);
    }

    public function update(GradeRequest $request, Grade $grade): RedirectResponse
    {
        $grade->update($request->validated());

        return redirect()
            ->route('gradebook.sheet', [$grade->student->class_id, $grade->subject_id])
            ->with('success', 'Ocena została zmieniona.');
    }

    public function destroy(Request $request, Grade $grade): RedirectResponse
    {
        abort_unless($request->user()->canGrade($grade->student->class_id, $grade->subject_id), 403);

        $grade->delete();

        return redirect()
            ->route('gradebook.sheet', [$grade->student->class_id, $grade->subject_id])
            ->with('success', 'Ocena została usunięta. Wpis pozostaje w historii.');
    }
}
