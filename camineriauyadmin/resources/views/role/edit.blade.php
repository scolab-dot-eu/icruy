@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                Actualizar rol
            </h3>
            {{ Form::model( $role, ['route' => ['roles.update', $role->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('role._fields')
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

