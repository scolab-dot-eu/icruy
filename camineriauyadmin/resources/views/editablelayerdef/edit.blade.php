@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{__('Consultar capa del inventario') }}
            </h3>
            {{ Form::model( $editablelayerdef, ['route' => ['editablelayerdefs.update', $editablelayerdef->id], 'method' => 'put', 'role' => 'form'] ) }}
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('name', 'Nombre') !!}
                            {!! Form::text('name', null, ['readonly' => '', 'class' => 'form-control']) !!}
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
                <div class="form-group">
                    {!! Form::label('fields', 'Definición de campos') !!}
                    <div class="alert alert-primary" role="alert">
                      Atención: Los cambios en la definición de los campos no modifican la tabla en base
                      de datos. Es responsabilidad del administrador modificar la tabla de forma coherente
                      con la definición. Los cambios en la definición de los dominios sí tendrán
                      efecto en la validación de futuras ediciones de la capa. 
                    </div>
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
                <br>
                <a href="{!! route('editablelayerdefs.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                {{ Form::submit('Guardar', ['class' => 'btn btn-info']) }}
                {{-- <!-- Form::submit('Cerrar', ['class' => 'btn btn-info'])  -->  --}}
                <!--  <a class="btn btn-info" href="{{url()->previous()}}" role="button">{{ __('Cerrar') }}</a>  -->
            </div>
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

