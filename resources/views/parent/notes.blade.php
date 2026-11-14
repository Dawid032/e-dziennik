@extends('layouts.app')

@section('title', 'Uwagi: '.$student->full_name)

@section('content')
    @include('parent._header')
    @include('partials.notes-list')

    {{ $notes->links() }}
@endsection
