@include('partials.form-errors')

<x-input name="name" label="Nazwa przedmiotu" :value="$subject->name" required maxlength="80"
    :pattern="\App\Support\ValidationPatterns::SUBJECT_NAME" title="Nazwa zaczynająca się od litery" />

<div class="actions">
    <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('admin.subjects.index') }}">Anuluj</a>
</div>
