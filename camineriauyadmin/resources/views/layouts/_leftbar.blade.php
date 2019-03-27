    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>{{ __('Caminería Rural') }}</h3>
        </div>
        <div class="sidebar-subheader">
            <h5>{{ __('Panel de control') }}</h5>
        </div>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                         aria-expanded="{!! (Route::is(['changerequests.*', 'mtopchangerequests.*'])) ? 'true' : 'false' !!}" aria-controls="collapseOne">
                      {{ __('Inventario') }}
                    </button>
                </h5>
            </div>
            <div id="collapseOne" class="collapse{!! (Route::is(['home', 'changerequests.*', 'mtopchangerequests.*', 'interventions.*', 'reports.*', 'imports.*'])) ? ' show' : '' !!}" aria-labelledby="headingOne" data-parent="#sidebar">
                <div class="card-body">
                <ul class="list-group list-group-flush" id="permissionsSubmenu">
                    <li class="{!! (Route::is('home')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('home') !!}">{{ __('Resumen') }}</a>
                    </li>
                    <li class="{!! (Route::is('changerequests.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('changerequests.index') !!}">{{ __('Peticiones de cambios') }}</a>
                    </li>
                    <li class="{!! (Route::is('mtopchangerequests.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('mtopchangerequests.index') !!}">{{ __('Peticiones de cambios MTOP') }}</a>
                    </li>
                    <li class="{!! (Route::is('interventions.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('interventions.index') !!}">{{ __('Intervenciones') }}</a>
                    </li>
                    <li class="{!! (Route::is('reports.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('reports.query') !!}">{{ __('Reportes') }}</a>
                    </li>
                    <li class="{!! (Route::is('imports.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('imports.query') !!}">{{ __('Importación de datos') }}</a>
                    </li>
                </ul>
                </div>
            </div>
        </div>
        @if (Auth::user()->isAdmin())
        <div class="card">
            <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseTwo"
                         aria-expanded="{!! (Route::is(['users.*', 'editablelayerdefs.*', 'roles.*'])) ? 'true' : 'false' !!}" aria-controls="collapseTwo">
                      {{ __('Configuración y permisos') }}
                    </button>
                </h5>
            </div>
            <div id="collapseTwo" class="collapse{!! (Route::is(['users.*', 'editablelayerdefs.*', 'supportlayerdefs.*', 'roles.*'])) ? ' show' : '' !!}" aria-labelledby="headingTwo" data-parent="#sidebar">
                <div class="card-body">
                <ul class="list-group list-group-flush" id="permissionsSubmenu">
                    <!--  <li class="{!! (Route::is('roles.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="{!! route('roles.index') !!}">{{ __('Roles') }}</a>
                    </li> -->
                    <li class="{!! (Route::is('users.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="/dashboard/users">{{ __('Usuarios') }}</a>
                    </li>
                    <li class="{!! (Route::is('editablelayerdefs.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="/dashboard/editablelayerdefs">{{ __('Capas del inventario') }}</a>
                    </li>
                    <li class="{!! (Route::is('supportlayerdefs.*')) ? ' active' : '' !!}">
                        <a class="list-group-item" href="/dashboard/supportlayerdefs">{{ __('Capas base o de apoyo') }}</a>
                    </li>
                </ul>
                </div>
            </div>
        </div>
        @endif
    </nav>