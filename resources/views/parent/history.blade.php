@extends('layouts.app')

@section('title', 'Historia ocen: '.$student->full_name)

@section('content')
    @include('parent._header')
    @include('partials.grade-history')

    {{ $history->links() }}
@endsection
