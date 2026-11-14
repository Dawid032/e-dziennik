@extends('layouts.app')

@section('title', 'Edycja przydziału')

@section('content')
    <div class="page-head"><h1>Edycja przydziału</h1></div>

    <form class="card form" method="post" action="{{ route('admin.assignments.update', $assignment) }}">
        @csrf
        @method('PUT')
        @include('admin.assignments._form', ['submitLabel' => 'Zapisz zmiany'])
    </form>
@endsection
