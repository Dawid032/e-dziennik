<div class="field">
    <label for="q">{{ $label ?? 'Szukaj' }}</label>
    <input id="q" type="search" name="q" value="{{ is_string(request('q')) ? request('q') : '' }}" placeholder="{{ $placeholder ?? '' }}">
</div>
