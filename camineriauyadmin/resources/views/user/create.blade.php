@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                Nuevo usuario
            </h3>
            {{ Form::model( $user, ['route' => ['users.store'], 'method' => 'post', 'role' => 'form'] ) }}
                @include('user._fields')
                {!! Form::submit(__('Guardar'), ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

