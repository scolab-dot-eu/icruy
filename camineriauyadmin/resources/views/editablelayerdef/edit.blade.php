@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                {{__('Consultar capa del inventario') }}
            </h3>
            {{ Form::model( $editablelayerdef, ['route' => ['editablelayerdefs.update', $editablelayerdef->id], 'method' => 'put', 'role' => 'form'] ) }}
                <div class="form-group">
                    {!! Form::label('name', 'Nombre') !!}
                    {!! Form::text('name', null, ['readonly' => '', 'class' => 'form-control']) !!}
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
                <div class="form-group">
                    {!! Form::label('fields', 'Definición de campos') !!}
                    {!! Form::text('fields', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('geom_style', 'Tipo de estilo') !!}
                    {!! Form::text('geom_style', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('style', 'Definición del estilo') !!}
                    {!! Form::textarea('style', null, ['class' => 'form-control']) !!}
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
                {{ Form::submit('Guardar', ['class' => 'btn btn-info']) }}
                {{-- <!-- Form::submit('Cerrar', ['class' => 'btn btn-info'])  -->  --}}
                <!--  <a class="btn btn-info" href="{{url()->previous()}}" role="button">{{ __('Cerrar') }}</a>  -->
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

