@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Resumen') }}</h2>
    <h4 class="titulo-resumen dashboard-text">{{ __('Peticiones') }}</h4>
    <div  class="dashboard-text" class="container">
      @if (Auth::user()->isAdmin())
      <div class="row">
        <div class="col">
            Existen <a href="{!! route('changerequests.index') !!}"><span style="font-weight: bold;">{{ $allOpen }}</span> peticiones</a> pendientes de validar. 
        </div>
      </div>
      @else
      <div class="row">
        <div class="col">
            Tienes <a href="{!! route('changerequests.index') !!}"><span style="font-weight: bold;">{{ $userOpen }}</span> peticiones</a> abiertas.
        </div>
      </div>
    </div>
      @endif
    <h4 class="titulo-resumen dashboard-text">{{ __('Departamentos') }}</h4>
    <ul class="list-group">
        @foreach($departments as $dep)
        <li class="list-group-item">
            <a style="color: rgba(0, 0, 0, 0.5);" href="/visor?departamento={{$dep->code}}"><i class="fa fa-lg fa-caret-right pull-right" style="color: orange;"></i> {{$dep->code}} - {{$dep->name}} </a>
        </li>
        @endforeach
    </ul>
@endsection
