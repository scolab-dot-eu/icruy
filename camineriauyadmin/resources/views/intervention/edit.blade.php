@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{ __('Actualizar intervención') }}
            </h3>
            {{ Form::model( $intervention, ['route' => ['interventions.update', $intervention->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('intervention._fields')
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

@section('custom_scripts')
<script type="text/javascript">
    $( document ).ready(function() {
        $('#tarea_es').editableSelect();
        $('#tarea_es').on('select.editable-select', function (e, el) {
            if (el!=null) {
                $('#tarea').val(el.attr('value'));
            }
        });
    });
</script>
@endsection
