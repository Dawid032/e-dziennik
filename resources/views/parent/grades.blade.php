@extends('layouts.app')

@section('title', 'Oceny: '.$student->full_name)

@section('content')
    @include('parent._header')
    @include('partials.student-grades')
@endsection
