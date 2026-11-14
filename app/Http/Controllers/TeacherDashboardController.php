<?php

namespace App\Http\Controllers;

use App\Models\GradeHistory;
use App\Services\GradeAverageCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $teacher = $request->user();

        return view('teacher.dashboard', [
            'assignments' => $teacher->teachingAssignments()
                ->with(['schoolClass' => fn ($query) => $query->withCount('students'), 'subject'])
                ->ordered()
                ->get(),
            'averages' => GradeAverageCalculator::classSubjectAverages(),
            'homeroomClasses' => $teacher->homeroomClasses()->withCount('students')->orderBy('name')->get(),
            'history' => GradeHistory::with(['grade.student', 'grade.subject', 'author'])
                ->visibleToTeacher($teacher)
                ->latest('id')
                ->limit(8)
                ->get(),
        ]);
    }
}
