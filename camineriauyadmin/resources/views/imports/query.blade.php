@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Importación de datos') }}</h2>
    <div  class="dashboard-text" class="container">
        {!! Form::open(['url' => route('imports.import'), 'files' => 'true', 'role' => 'form']) !!}
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('layer', __('Capa')) !!}
                    {!! Form::select('layer', $inventory_layers, null, ['class' => 'form-control es-input', 'required' => false]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">
                <div class="form-group">
                    <div class="custom-file">
                    {!! Form::label('importfile', __('Seleccionar fichero...'), ['class' => 'custom-file-label']) !!}
                    {!! Form::file('importfile', ['class' => 'custom-file-input', 'required' => true]) !!}
                    <!-- {!! Form::file('importfile[]', ['class' => 'custom-file-input', 'required' => true, 'multiple' => true]) !!}  -->
                    </div>
                </div>
            </div>
            
        </div>
        @if (Session::has('message'))
        <div class="row">
            <div class="col">
                <br>
                <span class="text-success">{{ Session::get('message') }}</span>
                <br><br>
            </div>
            
        </div>
        @endif
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <a href="{!! route('home') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                    {!! Form::submit('Importar', ['class' => 'btn btn-info']) !!}
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection

@section('custom_scripts')
    <script>
        $('#importfile').on('change',function(){;
            var files = $(this)[0].files;
            var fileName = files[0].name;
            for (var i=1; i<files.length; i++) {
                fileName = fileName + "; " + files[i].name;
                $(this).prev('.custom-file-label').text(fileName);
            }
            $(this).prev('.custom-file-label').text(fileName);
        })
    </script>
@endsection