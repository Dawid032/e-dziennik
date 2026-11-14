@use('App\Support\ValidationPatterns', 'Patterns')

@include('partials.form-errors')

<div class="form-grid">
    <x-input name="name" label="Nazwa klasy" :value="$class->name" required maxlength="5" placeholder="np. 2B"
        :pattern="Patterns::CLASS_NAME" title="Cyfra 1–8 i wielka litera, np. 1A lub 3Ti" />
    <x-input name="school_year" label="Rok szkolny" :value="$class->school_year" required maxlength="9" placeholder="np. 2026/2027"
        :pattern="Patterns::SCHOOL_YEAR" title="Format 2026/2027" />
    <div class="wide">
        <x-select name="homeroom_teacher_id" label="Wychowawca" :options="$teachers" :value="$class->homeroom_teacher_id" placeholder="Brak wychowawcy" />
    </div>
</div>

<div class="actions">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('admin.classes.index') }}">Anuluj</a>
</div>
