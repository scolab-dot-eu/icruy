@extends('layouts.dashboard')

@section('content')

    <h2>{{ __('Usuarios') }}</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <td>ID</td>
                <td>{{ __('Nombre') }}</td>
                <td>{{ __('Email') }}</td>
                <td>{{ __('Teléfono') }}</td>
                <td>{{ __('Habilitado') }}</td>
                <!-- <td>{{ __('Email verificado') }}</td>  -->
                <td></td>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $value)
            <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ $value->email }}</td>
                <td>{{ $value->phone }}</td>
                <td>@if ($value->enabled) {{ __('Sí') }} @else {{ __('No') }} @endif</td>
                <!-- <td>{{ $value->email_verified_at }}</td>  -->
                <td>
                    <a class="btn btn-small btn-secondary" href="{{ URL::to('dashboard/users/' . $value->id . '/edit') }}">{{__('Editar') }}</a>
                    @if ($value->enabled)
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal" data-enabled="true" data-id="{{$value->id}}" data-name="{{$value->name}}">{{__('Deshabilitar') }}</button>
                    @else
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#deleteModal" data-enabled="false" data-id="{{$value->id}}" data-name="{{$value->name}}">{{__('Habilitar') }}</button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <a class="btn btn-small btn-info" href="{{ URL::to('dashboard/users/create') }}">Nuevo usuario</a>

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación</h5>
                    <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Deshabilitar elemento?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                    <button id="confirmDeleteButton" type="button" class="btn btn-danger">Confirmar</button>
                </div>
                <form id="deleteForm" method="POST" action="">
                    {{ csrf_field() }} {{ method_field('POST') }}
                </form>
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>

@endsection

@section('custom_scripts')
<script type="text/javascript">
    $( document ).ready(function() {
        $('#deleteModal').on('show.bs.modal', function (event) {
              var button = $(event.relatedTarget);
              var id = button.data('id');
              var enabled = button.data('enabled');
              if (enabled==true) {
                  var text = '¿Deshabilitar elemento "' + button.data('name') + '" (id: ' + id + ")?";
                  var action = '/dashboard/users/' + id + '/disable';
              }
              else {
                  var text = '¿Habilitar elemento "' + button.data('name') + '" (id: ' + id + ")?";
                  var action = '/dashboard/users/' + id + '/enable';
              }
              var modal = $(this);
              modal.find('.modal-body p').text(text);
              $('#deleteForm').attr('action', action);
            });
        $('#confirmDeleteButton').on('click', function(event) {
            $('#deleteForm').submit();
        });
    });
</script>
@endsection
