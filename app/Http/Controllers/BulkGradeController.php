<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkGradeRequest;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Wpisanie jednej oceny całej klasie naraz, np. po sprawdzianie. */
class BulkGradeController extends Controller
{
    public function create(Request $request, SchoolClass $class, Subject $subject): View
    {
        abort_unless($request->user()->canGrade($class->id, $subject->id), 403);

        return view('gradebook.bulk', [
            'class' => $class,
            'subject' => $subject,
            'students' => $class->students()->get(),
        ]);
    }

    public function store(BulkGradeRequest $request, SchoolClass $class, Subject $subject): RedirectResponse
    {
        $data = $request->validated();
        $values = array_filter($data['values'], fn ($value) => $value !== null);

        // Każda ocena przez Grade::create, żeby obserwator zapisał historię.
        DB::transaction(function () use ($data, $values, $subject, $request) {
            foreach ($values as $studentId => $value) {
                Grade::create([
                    'student_id' => $studentId,
                    'subject_id' => $subject->id,
                    'teacher_id' => $request->user()->id,
                    'value' => $value,
                    'weight' => $data['weight'],
                    'category' => $data['category'],
                    'description' => $data['description'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('gradebook.sheet', [$class, $subject])
            ->with('success', 'Zapisano oceny: '.count($values).'.');
    }
}
