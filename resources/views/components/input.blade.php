@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

@php($id = 'f-'.$name)

<div @class(['field', 'is-invalid' => $errors->has($name)])>
    <label for="{{ $id }}">{{ $label }}@if ($attributes->has('required')) <span class="req" aria-hidden="true">*</span>@endif</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes }}>
    @if ($help)
        <small>{{ $help }}</small>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
