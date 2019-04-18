@extends('layouts.dashboard')

@section('content')
    <h2>{{ __('Peticiones de cambios') }}</h2>
    <div class="container icr-status-filter-header">
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
    <table class="table table-striped table-bordered" id="dt-icr-index">
        <thead>
            <tr>
                <td>ID</td>
                <td>{{ __('Tabla') }}</td>
                <td>{{ __('Operación') }}</td>
                <td>{{ __('Estado') }}</td>
                <td>{{ __('Autor') }}</td>
                <td>{{ __('Email autor') }}</td>
                <td>{{ __('Departamento') }}</td>
                <td>{{ __('Fecha solicitud') }}</td>
                <td></td>
            </tr>
        </thead>
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

<script type="text/javascript">
$(document).on('icrDataTablesJsLibLoaded', function() {
    var formatDate = function(date, type) {
        var dateStr = "";
        if (date) {
            if (type == 'display' || type == 'filter') {
                var dateParts = date.split("-");
                if (dateParts.length == 3) {
                    return dateParts[2].split(" ")[0] + "/" + dateParts[1] + "/" + dateParts[0];
                }
            }
            return date;
        }
        return dateStr;
    };

    $.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) {
        if (settings.jqXHR.status) {
            if (settings.jqXHR.status != 200) {
                console.log("Error de la petición ajax cargando datatables");
                console.log("Http status code: "+settings.jqXHR.status);
                if (settings.jqXHR.status == 401) {
                    alert("La sesión ha expirado. Vuelva a iniciar sesión");
                    window.location = '{{ route("login") }}';
                }
            }
        }
    };
    
    var theTable = $('#dt-icr-index').DataTable({
        language: dataTablesSpanishLang,
        processing: true,
        serverSide: true,
        dom: "<'row'<'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            "url": '{!! route('changerequests.datatables') !!}?status={!! request()->input('status') !!}',
            "data": function (d) {
                var re = new RegExp("/", "g");
                d.search.value = d.search.value.replace(re, " ");
                return d;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'layer', name: 'layer' },
            { data: 'operation', name: 'operation',  render: function ( data, type, row ) {
                if (type == 'display' || type == 'filter') {
                    return row.operation_label;
                };
                return data;
            }},
            { data: 'status', name: 'status',  render: function ( data, type, row ) {
                if (type == 'display' || type == 'filter') {
                    if (typeof row.status_label == "string") {
                        return row.status_label.substr(0, 14);
                    }
                };
                return data;
            }},
            { data: 'author', name: 'author.name', render: function ( data, type, row ) {
                if (data.name && data.email) {
                    return data.name + " (" + data.email + ")";
                }
                return "";
            }},
            { data: 'author', name: 'author.email', searchable: true, visible: false},
            { data: 'departamento', name: 'departamento'},
            { data: 'created_at', name: 'created_at', render: formatDate},
            //{ data: 'created_at_formatted', name: 'created_at_formatted'},
            {
                data: null,
                searchable: false,
                render: function(data, type, row) {
                    var consultarBtn = '<a class="btn btn-small btn-secondary" href="' + '{{ URL::to("dashboard/changerequests/") }}/' + row.id + '/edit">{{ __("Consultar") }}</a>';
                    return consultarBtn;
                }
            }
        ]
    });
    var departments = {!! json_encode($all_departments, JSON_HEX_TAG) !!};
    var depSelect = $('<select class="es-input form-control form-control-sm"></select>');
    var depLabel = $('<label>Departamento</label>');
    for (const code in departments) {
        depSelect.append('<option value="'+code+'">'+departments[code]+'</option>');
    }
    depLabel.append(depSelect);
    //var depSelect = $('<label>Departamento<select class="es-input form-control form-control-sm"><option value=""></option><option value="Test">test</option></select></label>');
    $("#dt-icr-index_filter").prepend(depLabel);
    depSelect.change(function() {
        var selectedDepCode = $(this).val(); 
        theTable.column("departamento:name").search(selectedDepCode).draw();
        });
});
</script>
@endsection