@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Peticiones de cambios') }}</h2>
    <div class="container">
      <div class="row">
        <div class="col">
            <div id="status_filter_group" class="btn-group btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-secondary @if (request()->input('status')=='' || request()->input('status')=='open') active @endif">
                <input type="radio" name="open" id="btn_status_open" autocomplete="off" @if (request()->input('status')=='' || request()->input('status')=='open') checked @endif>{{ __('Abiertas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='closed') active @endif">
                <input type="radio" name="closed" id="btn_status_closed" autocomplete="off" @if (request()->input('status')=='open') checked @endif>{{ __('Cerradas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='pending') active @endif">
                <input type="radio" name="pending" id="btn_status_pending" autocomplete="off" @if (request()->input('status')=='pending') checked @endif>{{ __('Pendientes') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='all') active @endif">
                <input type="radio" name="all" id="btn_status_all" autocomplete="off" @if (request()->input('status')=='all') checked @endif>{{ __('Todas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='userinfo') active @endif">
                <input type="radio" name="userinfo" id="btn_status_userinfo" autocomplete="off" @if (request()->input('status')=='userinfo') checked @endif>{{ __('Info admin') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='admininfo') active @endif">
                <input type="radio" name="admininfo" id="btn_status_admininfo" autocomplete="off" @if (request()->input('status')=='admininfo') checked @endif>{{ __('Info usuario') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='validated') active @endif">
                <input type="radio" name="validated" id="btn_status_validated" autocomplete="off" @if (request()->input('status')=='validated') checked @endif>{{ __('Validadas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='rejected') active @endif">
                <input type="radio" name="rejected" id="btn_status_rejected" autocomplete="off" @if (request()->input('status')=='rejected') checked @endif>{{ __('Rechazadas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='cancelled') active @endif">
                <input type="radio" name="cancelled" id="btn_status_cancelled" autocomplete="off" @if (request()->input('status')=='cancelled') checked @endif>{{ __('Canceladas') }}
              </label>
            </div>
          </div>
      </div>
      <div class="row">
        <div class="col">
        </div>
      </div>
    </div>
    <table style="margin-top: 20px" class="table table-striped table-bordered">
        <thead>
            <tr>
                <td>ID</td>
                <td>{{ __('Tabla') }}</td>
                <td>{{ __('Operación') }}</td>
                <td>{{ __('Estado') }}</td>
                <td>{{ __('Autor') }}</td>
                <td>{{ __('Fecha solicitud') }}</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
        @foreach($changerequests as $value)
            <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->layer }}</td>
                <td>{{ $value->operationLabel }}</td>
                <td>{{ $value->statusLabel }}</td>
                <td>{{ $value->author->email }}</td>
                <td>{{ $value->created_at }}</td>
                <td>
                    <a class="btn btn-small btn-secondary" href="{{ URL::to('dashboard/changerequests/' . $value->id . '/edit') }}">{{ __('Editar') }}</a>
                    <!-- <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal" data-id="{{$value->id}}" data-name="{{$value->email}}">Borrar</button>  -->
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <!-- <a class="btn btn-small btn-info" href="{{ URL::to('dashboard/users/create') }}">Nuevo rol</a>  -->

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación de borrado</h5>
                    <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Borrar elemento?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                    <button id="confirmDeleteButton" type="button" class="btn btn-danger">Borrar</button>
                </div>
                <form id="deleteForm" method="POST" action="">
                    {{ csrf_field() }} {{ method_field('DELETE') }}
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
              var modal = $(this);
              var text = '¿Borrar elemento "' + button.data('name') + '" (id: ' + id + ")?";
              modal.find('.modal-body p').text(text);
              var action = '/dashboard/users/' + id;
              $('#deleteForm').attr('action', action);
            });
        $('#confirmDeleteButton').on('click', function(event) {
            $('#deleteForm').submit();
        });

        $('#status_filter_group input').each(function(index, elem) {
            $(elem).on('change', function() {
                window.location.href = window.location.pathname + "?status=" + elem.name;
            });
        });
    });
</script>
@endsection
