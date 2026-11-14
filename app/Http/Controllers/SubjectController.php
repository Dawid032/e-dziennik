<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->query('q');

        $subjects = Subject::withCount('assignments')
            ->when(is_string($term) && trim($term) !== '', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('admin.subjects.index', ['subjects' => $subjects]);
    }

    public function create(): View
    {
        return view('admin.subjects.create', ['subject' => new Subject]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Przedmiot został dodany.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', ['subject' => $subject]);
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Zmiany zostały zapisane.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        // withTrashed, bo klucz obcy widzi również oceny usunięte miękko.
        if (Grade::withTrashed()->where('subject_id', $subject->id)->exists()) {
            return back()->with('error', 'Nie można usunąć przedmiotu, który ma oceny.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Przedmiot został usunięty.');
    }
}
