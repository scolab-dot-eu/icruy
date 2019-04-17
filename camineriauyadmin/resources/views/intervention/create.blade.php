@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                Nueva intervención
            </h3>
            {{ Form::model( $intervention, ['route' => ['interventions.store'], 'method' => 'post', 'role' => 'form'] ) }}
                <div class="container">
                @include('intervention._fields')
                </div>
                {!! Form::submit(__('Guardar'), ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

@section('custom_scripts')
@endsection