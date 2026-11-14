<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\User;
use App\Services\GradeAverageCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel wychowawcy: tylko odczyt. */
class HomeroomController extends Controller
{
    public function show(Request $request, SchoolClass $class): View
    {
        abort_unless($request->user()->isHomeroomTeacherOf($class), 403);

        $students = $class->students()->with('grades')->get();

        // Kolumny tabeli: przedmioty, z których uczniowie klasy mają oceny.
        $subjects = $class->assignments()->with('subject')->get()
            ->pluck('subject')
            ->sortBy('name')
            ->values();

        return view('teacher.homeroom.show', [
            'class' => $class,
            'students' => $students,
            'subjects' => $subjects,
        ]);
    }

    public function student(Request $request, SchoolClass $class, User $student): View
    {
        abort_unless($request->user()->isHomeroomTeacherOf($class), 403);
        abort_unless($student->isStudent() && $student->class_id === $class->id, 404);

        return view('teacher.homeroom.student', [
            'class' => $class,
            'student' => $student,
            'subjects' => $student->gradesBySubject(),
            'overall' => GradeAverageCalculator::weighted($student->grades),
            'notes' => $student->notes()->with('teacher')->latest()->get(),
        ]);
    }
}
