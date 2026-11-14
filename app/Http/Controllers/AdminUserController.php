<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role');

        $users = User::with('schoolClass')
            ->search($request->query('q'))
            ->when(in_array($role, array_keys(User::ROLES), true), fn ($query) => $query->where('role', $role))
            ->when($request->filled('class'), fn ($query) => $query->where('class_id', $request->integer('class')))
            ->alphabetical()
            ->paginate(config('dziennik.per_page'))
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => User::ROLES,
            'classes' => SchoolClass::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData(new User));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = new User;
        $this->save($user, $request->validated());

        return redirect()->route('admin.users.show', $user)->with('success', 'Konto zostało utworzone.');
    }

    public function show(User $user): View
    {
        $user->load(['schoolClass', 'children.schoolClass', 'parents'])->loadCount('grades');

        return view('admin.users.show', ['user' => $user]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', $this->formData($user->load('children')));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($data['role'] !== $user->role) {
            $reason = $user->is($request->user())
                ? 'Nie możesz zmienić własnej roli.'
                : $user->roleChangeBlockReason();

            if ($reason) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['role' => 'Zmiana roli jest niemożliwa. '.$reason]);
            }
        }

        $this->save($user, $data);

        return redirect()->route('admin.users.show', $user)->with('success', 'Zmiany zostały zapisane.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Nie możesz usunąć własnego konta.');
        }

        if ($reason = $user->deletionBlockReason()) {
            return back()->with('error', 'Tego konta nie można usunąć. '.$reason);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Konto zostało usunięte.');
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'roles' => User::ROLES,
            'classes' => SchoolClass::orderBy('name')->get()->pluck('label', 'id'),
            'students' => User::ofRole(User::ROLE_STUDENT)->with('schoolClass')->alphabetical()->get(),
        ];
    }

    /** Wspólny zapis dla tworzenia i edycji konta. */
    private function save(User $user, array $data): void
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        // Puste hasło przy edycji oznacza „bez zmian”.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($data['role'] !== User::ROLE_STUDENT) {
            $data['class_id'] = null;
        }

        DB::transaction(function () use ($user, $data, $children) {
            $user->fill($data)->save();
            $user->children()->sync($data['role'] === User::ROLE_PARENT ? $children : []);
        });
    }
}
