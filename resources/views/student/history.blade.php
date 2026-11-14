@extends('layouts.app')

@section('title', 'Historia zmian ocen')

@section('content')
    <div class="page-head"><h1>Historia zmian moich ocen</h1></div>

    @include('partials.grade-history')

    {{ $history->links() }}
@endsection
