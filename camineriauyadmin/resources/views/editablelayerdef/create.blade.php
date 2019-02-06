@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{__('Nueva capa del inventario') }}
            </h3>
            {{ Form::model( $editablelayerdef, ['route' => ['editablelayerdefs.store'], 'method' => 'post', 'role' => 'form'] ) }}
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('name', 'Nombre') !!}
                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('title', 'Título') !!}
                            {!! Form::text('title', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('geom_type', 'Tipo de geometría') !!}
                            {!! Form::text('geom_type', null, ['readonly' => '', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('color', 'Color del estilo') !!}
                            {!! Form::text('color', $color, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <!-- 
                <div class="form-group">
                    {!! Form::label('protocol', 'Protocolo') !!}
                    {!! Form::text('protocol', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('url', 'URL') !!}
                    {!! Form::text('url', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('geom_type', 'Tipo de geometría') !!}
                    {!! Form::select('geom_type', ['point' => 'Punto', 'lineString' => 'Línea', 'polygon' => 'Polígono']); !!}
                </div> -->
                <div class="form-group">
                    {!! Form::label('fields', 'Definición de campos') !!}
                    {!! Form::textarea('fields', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-check">
                    {!! Form::checkbox('visible', '1', null, ['id' => 'visible', 'class' => 'form-check-input']) !!}
                    {!! Form::label('visible', __('Visible por defecto')) !!}
                </div>
                <div class="form-check">
                    {!! Form::checkbox('download', '1', null, ['id' => 'download', 'class' => 'form-check-input']) !!}
                    {!! Form::label('download', __('Descargable')) !!}
                </div>
                <div class="form-check">
                    {!! Form::checkbox('showTable', '1', null, ['id' => 'showTable', 'class' => 'form-check-input']) !!}
                    {!! Form::label('showTable', __('Mostrar tabla de atributos')) !!}
                </div>
                <div class="form-check">
                    {!! Form::checkbox('showInSearch', '1', null, ['id' => 'showInSearch', 'class' => 'form-check-input']) !!}
                    {!! Form::label('showInSearch', __('Mostrar en las búsquedas')) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('metadata', 'Enlace al metadato') !!}
                    {!! Form::text('metadata', null, ['class' => 'form-control']) !!}
                </div>
                <!-- 
                <div class="form-group">
                    {!! Form::label('conf', 'Configuración adicional') !!}
                    {!! Form::textarea('conf', null, ['class' => 'form-control']) !!}
                </div>-->
                <div class="form-group">
                </div>
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            </div>
            {{ Form::close() }}
            <br />
        </div>
    </div>

@endsection

