<?php

namespace App\Http\Controllers;

use App\Models\GradeHistory;
use App\Models\User;
use App\Services\GradeAverageCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel rodzica: dane wyłącznie przypisanych dzieci, tylko odczyt. */
class ParentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('parent.dashboard', [
            'children' => $request->user()->children()
                ->with(['schoolClass.homeroomTeacher', 'grades'])
                ->alphabetical()
                ->get(),
        ]);
    }

    public function grades(Request $request, User $student): View
    {
        $this->authorizeChild($request, $student);

        return view('parent.grades', [
            'student' => $student->load('schoolClass.homeroomTeacher'),
            'subjects' => $student->gradesBySubject(),
            'overall' => GradeAverageCalculator::weighted($student->grades),
        ]);
    }

    public function history(Request $request, User $student): View
    {
        $this->authorizeChild($request, $student);

        return view('parent.history', [
            'student' => $student->load('schoolClass.homeroomTeacher'),
            'history' => GradeHistory::with(['grade.subject', 'author'])
                ->forStudent($student->id)
                ->latest('id')
                ->paginate(config('dziennik.per_page')),
        ]);
    }

    public function notes(Request $request, User $student): View
    {
        $this->authorizeChild($request, $student);

        return view('parent.notes', [
            'student' => $student->load('schoolClass.homeroomTeacher'),
            'notes' => $student->notes()->with('teacher')->latest()->paginate(config('dziennik.per_page')),
        ]);
    }

    private function authorizeChild(Request $request, User $student): void
    {
        abort_unless($request->user()->children()->whereKey($student->id)->exists(), 403);
    }
}
