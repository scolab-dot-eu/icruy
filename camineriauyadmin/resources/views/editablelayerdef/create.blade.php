@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                {{__('Nueva capa del inventario') }}
            </h3>
            {{ Form::model( $editablelayerdef, ['route' => ['editablelayerdefs.store'], 'method' => 'post', 'role' => 'form'] ) }}
                <div class="form-group">
                    {!! Form::label('name', 'Nombre') !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('title', 'Título') !!}
                    {!! Form::text('title', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('geom_type', 'Tipo de geometría') !!}
                    {!! Form::text('geom_type', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('protocol', 'Protocolo') !!}
                    {!! Form::text('protocol', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('url', 'URL') !!}
                    {!! Form::text('url', null, ['class' => 'form-control']) !!}
                </div>
                <!-- 
                <div class="form-group">
                    {!! Form::label('geom_type', 'Tipo de geometría') !!}
                    {!! Form::select('geom_type', ['point' => 'Punto', 'lineString' => 'Línea', 'polygon' => 'Polígono']); !!}
                </div> -->
                <div class="form-group">
                    {!! Form::label('fields', 'Definición de campos') !!}
                    {!! Form::textarea('fields', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('metadata', 'Enlace al metadato') !!}
                    {!! Form::text('metadata', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('conf', 'Configuración adicional') !!}
                    {!! Form::textarea('conf', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                </div>
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

