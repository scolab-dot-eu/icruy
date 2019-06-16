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
    <div id="main-container" class="container-fluid" style="padding-left: 0px; padding-right: 0px;">

        <!-- Image and text -->
        <nav class="navbar navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('images/logo_opp2.png') }}" width="150" height="36" class="d-inline-block align-top" alt="">
                </a>
                <div class="navbar-nav mr-auto"></div>
                <div class="nav-item dropdown">
                @auth
                    <a class="nav-link dropdown-toggle" style="padding: 0.5rem 2.5rem; color: #fff;" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/dashboard/users">Panel de control</a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}"
                            class="dropdown-item"
                            onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            Cerrar sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                @else
                    <a class="nav-link" style="color: #fff;" href="{{ route('login') }}" role="button" aria-haspopup="true" aria-expanded="false">
                        Anónimo
                    </a>
                @endauth
                </div>
            </div>
            
        </nav>

        <!--
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    @auth
                        <li class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle nav-link" id="navbarDropdown" data-toggle="dropdown" role="button"  aria-haspopup="true" aria-expanded="false">
                                Hi, {{ Auth::user()->name }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </a>

                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropDown">
                                <li>
                                    <a href="{{ route('logout') }}"
                                        class="dropdown-item"
                                        onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Anónimo</a></li>
                    @endauth
                    </ul>
                </div>
            </div>
        </nav>
        -->
        <div class="container">
            <div style="margin-top: 30px;" class="row">
                <div style="color:#888888;" class="col">
                    <h3>Departamentos</h3>
                </div>
            </div>
  
            <div class="list-group">
                @foreach($departments as $dep)
                <li class="list-group-item">
                    <a style="color: rgba(0, 0, 0, 0.5);" href="visor?departamento={{$dep->code}}"><i class="fa fa-lg fa-caret-right pull-right" style="color: orange;"></i> {{$dep->code}} - {{$dep->name}} </a>
                </li>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script src="https://use.fontawesome.com/811fe8e43b.js"></script>
@yield('custom_scripts')
</body>
</html>
