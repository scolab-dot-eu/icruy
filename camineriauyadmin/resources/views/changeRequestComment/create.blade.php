@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col-md-8 col-sm-12">

            <h3>
                Nuevo departamento
            </h3>

            {!! Form::open(['route' => 'department.store']) !!}

            <div class="form-group">
                {!! Form::label('name', 'Nombre') !!}
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('desc', 'Descripción') !!}
                {!! Form::text('desc', null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('msg', 'Message') !!}
                {!! Form::textarea('msg', null, ['class' => 'form-control']) !!}
            </div>

            <div class="form-group">
            </div>

            {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}

            {!! Form::close() !!}
            <br />
        </div>

    </div>

@endsection

