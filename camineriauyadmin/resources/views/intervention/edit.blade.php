@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
            @if ($editable)
                {{ __('Actualizar intervención') }}
            @else
                {{ __('Consultar intervención') }}
            @endif
            </h3>
            {{ Form::model( $intervention, ['route' => ['interventions.update', $intervention->id], 'method' => 'put', 'role' => 'form'] ) }}
                <div class="container">
                @include('intervention._fields')
                @if ($changeRequestUrl)
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                          <div class="alert alert-primary" role="alert">
                            Atención: existe una <a href="{{ $changeRequestUrl }}">petición de cambios pendiente</a> para esta intervención. 
                          </div>
                      </div>
                    </div>
                  </div>
                @endif
                </div>
                <br>
                <a href="{!! route('interventions.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                @if ($editable)
                {!! Form::submit('Guardar', ['class' => 'btn btn-info']) !!}
                @endif
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
