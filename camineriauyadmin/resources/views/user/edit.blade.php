@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{ __('Actualizar usuario') }}
            </h3>
            {{ Form::model( $user, ['route' => ['users.update', $user->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('user._fields')
                <br>
                <a href="{!! route('users.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

