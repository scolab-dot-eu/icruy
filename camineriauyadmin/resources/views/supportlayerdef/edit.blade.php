@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                {{__('Editar capa base o de apoyo') }}
            </h3>
            {{ Form::model( $supportlayerdef, ['route' => ['supportlayerdefs.update', $supportlayerdef->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('supportlayerdef._fields')
                <br>
                <a href="{!! route('supportlayerdefs.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                {!! Form::submit(__('Guardar'), ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

