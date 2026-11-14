@extends('layouts.app')

@section('title', 'Uwagi')

@section('content')
    <div class="page-head"><h1>Pochwały i uwagi</h1></div>

    @include('partials.notes-list')

    {{ $notes->links() }}
@endsection
