<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeAverageCalculator;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'roleCounts' => User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role'),
            'classCount' => SchoolClass::count(),
            'subjectCount' => Subject::count(),
            'gradeCount' => Grade::count(),
            'classes' => SchoolClass::with('homeroomTeacher')->withCount('students')->orderBy('name')->get(),
            'averages' => GradeAverageCalculator::classAverages(),
            'history' => GradeHistory::with(['grade.student', 'grade.subject', 'author'])->latest('id')->limit(10)->get(),
        ]);
    }
}
