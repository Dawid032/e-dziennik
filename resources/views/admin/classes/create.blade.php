@extends('layouts.app')

@section('title', 'Nowa klasa')

@section('content')
    <div class="page-head"><h1>Nowa klasa</h1></div>

    <form class="card form" method="post" action="{{ route('admin.classes.store') }}">
        @csrf
        @include('admin.classes._form', ['submitLabel' => 'Dodaj klasę'])
    </form>
@endsection
