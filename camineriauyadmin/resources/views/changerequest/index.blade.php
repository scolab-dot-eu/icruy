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
              <!-- 
              <label class="btn btn-secondary @if (request()->input('status')=='pending') active @endif">
                <input type="radio" name="pending" id="btn_status_pending" autocomplete="off" @if (request()->input('status')=='pending') checked @endif>{{ __('Pendientes') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='userinfo') active @endif">
                <input type="radio" name="userinfo" id="btn_status_userinfo" autocomplete="off" @if (request()->input('status')=='userinfo') checked @endif>{{ __('Info admin') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='admininfo') active @endif">
                <input type="radio" name="admininfo" id="btn_status_admininfo" autocomplete="off" @if (request()->input('status')=='admininfo') checked @endif>{{ __('Info usuario') }}
              </label>
               -->
              <label class="btn btn-secondary @if (request()->input('status')=='validated') active @endif">
                <input type="radio" name="validated" id="btn_status_validated" autocomplete="off" @if (request()->input('status')=='validated') checked @endif>{{ __('Validadas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='rejected') active @endif">
                <input type="radio" name="rejected" id="btn_status_rejected" autocomplete="off" @if (request()->input('status')=='rejected') checked @endif>{{ __('Rechazadas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='cancelled') active @endif">
                <input type="radio" name="cancelled" id="btn_status_cancelled" autocomplete="off" @if (request()->input('status')=='cancelled') checked @endif>{{ __('Canceladas') }}
              </label>
              <label class="btn btn-secondary @if (request()->input('status')=='all') active @endif">
                <input type="radio" name="all" id="btn_status_all" autocomplete="off" @if (request()->input('status')=='all') checked @endif>{{ __('Todas') }}
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
                <td>{{ $value->created_at_formatted }}</td>
                <td>
                    <a class="btn btn-small btn-secondary" href="{{ URL::to('dashboard/changerequests/' . $value->id . '/edit') }}">{{ __('Consultar') }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="row">&nbsp;</div>

@endsection

@section('custom_scripts')
<script type="text/javascript">
    $( document ).ready(function() {
        $('#status_filter_group input').each(function(index, elem) {
            $(elem).on('change', function() {
                window.location.href = window.location.pathname + "?status=" + elem.name;
            });
        });
    });
</script>
@endsection
