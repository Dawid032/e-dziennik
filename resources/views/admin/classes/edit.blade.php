@extends('layouts.app')

@section('title', 'Edycja klasy')

@section('content')
    <div class="page-head"><h1>Edycja klasy {{ $class->name }}</h1></div>

    <form class="card form" method="post" action="{{ route('admin.classes.update', $class) }}">
        @csrf
        @method('PUT')
        @include('admin.classes._form', ['submitLabel' => 'Zapisz zmiany'])
    </form>
@endsection
