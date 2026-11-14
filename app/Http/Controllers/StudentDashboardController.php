<?php

namespace App\Http\Controllers;

use App\Models\GradeHistory;
use App\Services\GradeAverageCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel ucznia: wyłącznie własne dane, tylko odczyt. */
class StudentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->load('schoolClass.homeroomTeacher');

        return view('student.dashboard', [
            'student' => $student,
            'overall' => GradeAverageCalculator::weighted($student->grades),
            'latestGrades' => $student->grades()->with('subject')->latest()->limit(5)->get(),
            'latestNotes' => $student->notes()->with('teacher')->latest()->limit(3)->get(),
        ]);
    }

    public function grades(Request $request): View
    {
        $student = $request->user();

        return view('student.grades', [
            'subjects' => $student->gradesBySubject(),
            'overall' => GradeAverageCalculator::weighted($student->grades),
        ]);
    }

    public function history(Request $request): View
    {
        return view('student.history', [
            'history' => GradeHistory::with(['grade.subject', 'author'])
                ->forStudent($request->user()->id)
                ->latest('id')
                ->paginate(config('dziennik.per_page')),
        ]);
    }

    public function notes(Request $request): View
    {
        return view('student.notes', [
            'notes' => $request->user()->notes()->with('teacher')->latest()->paginate(config('dziennik.per_page')),
        ]);
    }
}
