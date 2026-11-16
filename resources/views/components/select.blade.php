@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null])

@php
    $id = 'f-'.$name;
    $selected = old($name, $value);
    $selected = is_scalar($selected) ? (string) $selected : '';
@endphp

<div @class(['field', 'is-invalid' => $errors->has($name)])>
    <label for="{{ $id }}">{{ $label }}@if ($attributes->get('required')) <span class="req" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
