<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->query('q');

        $classes = SchoolClass::with('homeroomTeacher')
            ->withCount('students')
            ->when(is_string($term) && trim($term) !== '', function ($query) use ($term) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('school_year', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('admin.classes.index', ['classes' => $classes]);
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData(new SchoolClass));
    }

    public function store(SchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::create($request->validated());

        return redirect()->route('admin.classes.index')->with('success', 'Klasa została dodana.');
    }

    public function edit(SchoolClass $class): View
    {
        return view('admin.classes.edit', $this->formData($class));
    }

    public function update(SchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $class->update($request->validated());

        return redirect()->route('admin.classes.index')->with('success', 'Zmiany zostały zapisane.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->students()->exists()) {
            return back()->with('error', 'Nie można usunąć klasy, która ma uczniów.');
        }

        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Klasa została usunięta.');
    }

    private function formData(SchoolClass $class): array
    {
        return [
            'class' => $class,
            'teachers' => User::ofRole(User::ROLE_TEACHER)->alphabetical()->get()->pluck('full_name', 'id'),
        ];
    }
}
