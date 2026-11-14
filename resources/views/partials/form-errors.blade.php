@if ($errors->any())
    <div class="error-summary" role="alert">
        <strong>Popraw błędy w formularzu:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
