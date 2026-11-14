@props(['grade', 'href' => null])

@if ($href)
    <a class="grade grade-{{ $grade->value }}" href="{{ $href }}" title="{{ $grade->tooltip }}">{{ $grade->value }}</a>
@else
    <span class="grade grade-{{ $grade->value }}" title="{{ $grade->tooltip }}">{{ $grade->value }}</span>
@endif
