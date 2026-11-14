@include('partials.form-errors')

<div class="form-grid">
    <x-select name="class_id" label="Klasa" :options="$classes" :value="$assignment->class_id" placeholder="Wybierz klasę" required />
    <x-select name="subject_id" label="Przedmiot" :options="$subjects" :value="$assignment->subject_id" placeholder="Wybierz przedmiot" required />
    <div class="wide">
        <x-select name="teacher_id" label="Nauczyciel" :options="$teachers" :value="$assignment->teacher_id" placeholder="Wybierz nauczyciela" required />
    </div>
</div>

<div class="actions">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('admin.assignments.index') }}">Anuluj</a>
</div>
