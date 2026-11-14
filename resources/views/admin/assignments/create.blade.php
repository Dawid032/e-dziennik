@extends('layouts.app')

@section('title', 'Nowy przydział')

@section('content')
    <div class="page-head"><h1>Nowy przydział</h1></div>

    <form class="card form" method="post" action="{{ route('admin.assignments.store') }}">
        @csrf
        @include('admin.assignments._form', ['submitLabel' => 'Dodaj przydział'])
    </form>
@endsection
