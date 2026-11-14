<?php

namespace App\Http\Controllers;

use App\Models\GradeHistory;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $action = $request->query('action');

        $history = GradeHistory::with(['grade.student.schoolClass', 'grade.subject', 'author'])
            ->when($user->isTeacher(), fn ($query) => $query->visibleToTeacher($user))
            ->when($request->filled('subject'), function ($query) use ($request) {
                $query->whereHas('grade', fn ($grade) => $grade->where('subject_id', $request->integer('subject')));
            })
            ->when(in_array($action, array_keys(GradeHistory::ACTIONS), true), fn ($query) => $query->where('action', $action))
            ->latest('id')
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        $subjects = Subject::query()
            ->when($user->isTeacher(), fn ($query) => $query->whereIn('id', $user->teachingAssignments()->pluck('subject_id')))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('history.index', [
            'history' => $history,
            'subjects' => $subjects,
            'actions' => GradeHistory::ACTIONS,
        ]);
    }
}
