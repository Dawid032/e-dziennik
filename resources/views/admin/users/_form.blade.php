@use('App\Support\ValidationPatterns', 'Patterns')

@php
    $isOwnAccount = $user->exists && $user->is(auth()->user());
    $selectedChildren = array_map('intval', (array) old('children', $user->exists ? $user->children->pluck('id')->all() : []));
@endphp

@include('partials.form-errors')

<div class="form-grid">
    <x-input name="first_name" label="Imię" :value="$user->first_name" required maxlength="60"
        :pattern="Patterns::NAME" title="Wielka litera na początku, tylko litery" />
    <x-input name="last_name" label="Nazwisko" :value="$user->last_name" required maxlength="80"
        :pattern="Patterns::NAME" title="Wielka litera na początku, tylko litery" />
    <x-input name="email" label="E-mail" type="email" :value="$user->email" required maxlength="150" />

    @if ($isOwnAccount)
        <input type="hidden" name="role" value="{{ $user->role }}">
        <div class="field">
            <span class="field-label">Rola</span>
            <span>{{ $user->role_name }} <small>(nie możesz zmienić własnej roli)</small></span>
        </div>
    @else
        <x-select name="role" label="Rola" :options="$roles" :value="$user->role" placeholder="Wybierz rolę" required data-role-select />
    @endif

    <x-input name="password" label="{{ $user->exists ? 'Nowe hasło' : 'Hasło' }}" type="password" maxlength="72" autocomplete="new-password"
        :required="! $user->exists" :pattern="Patterns::PASSWORD" title="Co najmniej 8 znaków, w tym litera i cyfra"
        help="{{ $user->exists ? 'Zostaw puste, aby nie zmieniać hasła.' : 'Co najmniej 8 znaków, w tym litera i cyfra.' }}" />
    <x-input name="password_confirmation" label="Powtórz hasło" type="password" autocomplete="new-password" :required="! $user->exists" />

    @unless ($isOwnAccount)
        <div class="wide" data-role-field="student">
            <x-select name="class_id" label="Klasa ucznia" :options="$classes" :value="$user->class_id" placeholder="Wybierz klasę" />
        </div>

        <div class="wide" data-role-field="parent">
            <div @class(['field', 'is-invalid' => $errors->has('children')])>
                <span class="field-label">Dzieci rodzica</span>
                <div class="check-list">
                    @forelse ($students as $student)
                        <label>
                            <input type="checkbox" name="children[]" value="{{ $student->id }}" @checked(in_array($student->id, $selectedChildren, true))>
                            {{ $student->last_name }} {{ $student->first_name }} ({{ $student->schoolClass?->name ?? 'bez klasy' }})
                        </label>
                    @empty
                        <span class="muted">Najpierw utwórz konta uczniów.</span>
                    @endforelse
                </div>
                @error('children')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @endunless
</div>

<div class="actions">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('admin.users.index') }}">Anuluj</a>
</div>
