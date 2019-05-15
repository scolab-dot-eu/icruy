@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Resumen') }}</h2>
    <div  class="dashboard-text" class="container">
      @if (Auth::check())
      <h4 class="titulo-resumen dashboard-text">{{ __('Peticiones') }}</h4>
          @if (Auth::user()->isAdmin())
          <div class="row">
            <div class="col">
                Existen <a href="{!! route('changerequests.index') !!}"><span style="font-weight: bold;">{{ $allOpen }}</span> peticiones</a> pendientes de validar. 
            </div>
          </div>
          @elseif (Auth::user()->isMtopManager())
          <div class="row">
            <div class="col">
                Existen <a href="{!! route('mtopchangerequests.index') !!}"><span style="font-weight: bold;">{{ $mtopAllOpen }}</span> peticiones MTOP</a> pendientes de validar. 
            </div>
          </div>
          @else
          <div class="row">
            <div class="col">
                Tienes <a href="{!! route('changerequests.index') !!}"><span style="font-weight: bold;">{{ $userOpen }}</span> peticiones</a> abiertas.
            </div>
          </div>
          <div class="row">
            <div class="col">
                Tienes <a href="{!! route('mtopchangerequests.index') !!}"><span style="font-weight: bold;">{{ $userMtopOpen }}</span> peticiones MTOP</a> abiertas.
            </div>
          </div>
          @endif
        </div>
        <h4 class="titulo-resumen dashboard-text">{{ __('Departamentos') }}</h4>
        <ul class="list-group">
            @foreach($departments as $dep)
            <li class="list-group-item">
                <a style="color: rgba(0, 0, 0, 0.5);" href="/visor?departamento={{$dep->code}}"><i class="fa fa-lg fa-caret-right pull-right" style="color: orange;"></i> {{$dep->code}} - {{$dep->name}} </a>
            </li>
            @endforeach
        </ul>
      @else
      <div class="row">
        <div class="col">
          <a style="color: rgba(0, 0, 0, 0.5);" href="/visor"><i class="fa fa-lg fa-caret-right pull-right" style="color: orange;"></i> Volver al visor global </a> 
        </div>
      </div>
      @endif
@endsection
