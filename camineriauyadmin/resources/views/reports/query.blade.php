@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Reportes') }}</h2>
    <div  class="dashboard-text" class="container">
        {!! Form::open(['url' => route('reports.download'), 'role' => 'form']) !!}
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('tipo_reporte', __('Tipo reporte')) !!}
                    {!! Form::select('tipo_reporte', ['detalle'=>'Detalle','resumen'=>'Resumen'], null, ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('ambito', __('Ámbito geográfico')) !!}
                    {!! Form::select('ambito', $user_departments, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    {!! Form::label('from_year', __('Desde año')) !!}
                    {!! Form::number('from_year', null, ['class' => 'form-control', 'step' => '1', 'min' => '1811']) !!}
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    {!! Form::label('to_year', __('Hasta año')) !!}
                    {!! Form::number('to_year', null, ['class' => 'form-control', 'step' => '1', 'min' => '1811']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('tipo_elem', __('Tipo elemento')) !!}
                    {!! Form::select('tipo_elem', $inventory_layers, null, ['class' => 'form-control es-input', 'required' => false]) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('codigo_camino', __('Código camino')) !!}
                    {!! Form::text('codigo_camino', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('id_elem', __('Código elemento')) !!}
                    {!! Form::number('id_elem', null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('tarea', __('Tarea')) !!}
                    {!! Form::select('tarea', $tareaSelect, null, ['class' => 'form-control es-input']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('financiacion', __('Financiación')) !!}
                    {!! Form::select('financiacion', $financiacionSelect, null, ['class' => 'form-control es-input']) !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('forma_ejecucion', __('Forma ejecución')) !!}
                    {!! Form::select('forma_ejecucion', $formaEjecucionSelect, null, ['class' => 'form-control es-input']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('format', __('Formato')) !!}
                    {!! Form::select('format', ['xlsx'=>'Excel XML (.xlsx)', 'ods'=> 'LibreOffice OpenDocument (.ods)', 'csv'=>'CSV'], 'xlsx', ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <a href="{!! route('home') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                    {!! Form::submit('Generar', ['class' => 'btn btn-info']) !!}
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
