<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeachingAssignmentRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $assignments = TeachingAssignment::with(['schoolClass', 'subject', 'teacher'])
            ->search($request->query('q'))
            ->ordered()
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('admin.assignments.index', ['assignments' => $assignments]);
    }

    public function create(): View
    {
        return view('admin.assignments.create', $this->formData(new TeachingAssignment));
    }

    public function store(TeachingAssignmentRequest $request): RedirectResponse
    {
        TeachingAssignment::create($request->validated());

        return redirect()->route('admin.assignments.index')->with('success', 'Przydział został dodany.');
    }

    public function edit(TeachingAssignment $assignment): View
    {
        return view('admin.assignments.edit', $this->formData($assignment));
    }

    public function update(TeachingAssignmentRequest $request, TeachingAssignment $assignment): RedirectResponse
    {
        $assignment->update($request->validated());

        return redirect()->route('admin.assignments.index')->with('success', 'Zmiany zostały zapisane.');
    }

    public function destroy(TeachingAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('admin.assignments.index')->with('success', 'Przydział został usunięty.');
    }

    private function formData(TeachingAssignment $assignment): array
    {
        return [
            'assignment' => $assignment,
            'classes' => SchoolClass::orderBy('name')->get()->pluck('label', 'id'),
            'subjects' => Subject::orderBy('name')->pluck('name', 'id'),
            'teachers' => User::ofRole(User::ROLE_TEACHER)->alphabetical()->get()->pluck('full_name', 'id'),
        ];
    }
}
