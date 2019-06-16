@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                {{__('Nueva capa base o de apoyo') }}
            </h3>
            {{ Form::model( $supportlayerdef, ['route' => ['supportlayerdefs.store'], 'method' => 'post', 'role' => 'form'] ) }}
                @include('supportlayerdef._fields')
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

