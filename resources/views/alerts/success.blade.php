@if (session($key ?? 'status'))
    <div class="alert  alert-light" role="alert">
        {{ session($key ?? 'status') }}
    </div>
@endif
