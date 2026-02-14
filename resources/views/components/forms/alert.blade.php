@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                @if (!empty($error))
                    <li>{{ $error }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif
