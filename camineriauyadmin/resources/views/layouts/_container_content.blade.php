<notifications group="favorites" position="bottom right"></notifications>
<notifications group="tickets" position="bottom right"></notifications>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@yield('content')