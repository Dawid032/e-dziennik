@extends('layouts.app')

@section('title', 'Nowy przedmiot')

@section('content')
    <div class="page-head"><h1>Nowy przedmiot</h1></div>

    <form class="card form" method="post" action="{{ route('admin.subjects.store') }}">
        @csrf
        @include('admin.subjects._form', ['submitLabel' => 'Dodaj przedmiot'])
    </form>
@endsection
