@extends('layouts.app')

@section('title', 'Nowy wpis')

@section('content')
    <div class="page-head"><h1>Nowa pochwała lub uwaga</h1></div>

    <form class="card form" method="post" action="{{ route('notes.store') }}">
        @csrf
        @include('partials.form-errors')

        <div class="form-grid">
            <x-select name="student_id" label="Uczeń" :options="$students" :value="$selectedStudent" placeholder="Wybierz ucznia" required />
            <x-select name="type" label="Rodzaj" :options="$types" placeholder="Wybierz rodzaj" required />
            <div class="wide">
                <x-textarea name="content" label="Treść" required maxlength="1000" />
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Zapisz</button>
            <a class="btn" href="{{ route('notes.index') }}">Anuluj</a>
        </div>
    </form>
@endsection
