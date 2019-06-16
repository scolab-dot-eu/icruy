<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Camineria Rural') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app" class="wrapper">

    @include('layouts._leftbar')
    <div id="main-container" class="container-fluid" style="padding-left: 0px: padding-right: 0px;">
        @include('layouts._headerbar')
        <div class="container">
            @include('layouts._container_content')
		    @include('layouts._footer')
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
@yield('custom_scripts')
</body>
</html>
