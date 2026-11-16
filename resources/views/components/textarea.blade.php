@props(['name', 'label', 'value' => null])

@php($id = 'f-'.$name)

<div @class(['field', 'is-invalid' => $errors->has($name)])>
    <label for="{{ $id }}">{{ $label }}@if ($attributes->get('required')) <span class="req" aria-hidden="true">*</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
