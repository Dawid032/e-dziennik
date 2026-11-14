@extends('layouts.app')

@section('title', 'Edycja przedmiotu')

@section('content')
    <div class="page-head"><h1>Edycja: {{ $subject->name }}</h1></div>

    <form class="card form" method="post" action="{{ route('admin.subjects.update', $subject) }}">
        @csrf
        @method('PUT')
        @include('admin.subjects._form', ['submitLabel' => 'Zapisz zmiany'])
    </form>
@endsection
