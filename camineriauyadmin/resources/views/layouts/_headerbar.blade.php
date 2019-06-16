<nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-static-top">
    <button type="button" id="sidebarCollapse" class="btn btn-secondary">
        <span class="fa fa-bars"></span>
    </button>
    <div class="container">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @auth
                    <li class="nav-item dropdown">
                        <a href="#" class="dropdown-toggle nav-link" id="navbarDropdown" data-toggle="dropdown" role="button"  aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </a>

                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropDown">
                            <li>
                                <a href="{{ route('logout') }}"
                                   class="dropdown-item"
                                   onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                                    Cerrar sesión
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
                <!--
                <li class="nav-item">
                    @{!! \App\Helpers\Helpers::link_to_route_html('search.index','<i class="fa fa-search"></i>', null, ['class' => 'nav-link']) !!}
                </li>
                -->
            </ul>

        </div>
    </div>
</nav>
