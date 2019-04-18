@extends('layouts.dashboard')

@section('content')

    <h2>{{ __('Usuarios') }}</h2>
    <table class="table table-striped table-bordered" id="dt-icr-index">
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

<script type="text/javascript">
$(document).on('icrDataTablesJsLibLoaded', function() {
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
        dom: "<'row'<'col-sm-12 col-md-12'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            "url": '{!! route("users.datatables") !!}',
            "data": function (d) {
                var re = new RegExp("/", "g");
                d.search.value = d.search.value.replace(re, " ");
                return d;
            }
        },
        order: [[1, 'asc'],[4, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'enabled', name: 'enabled', render: function ( data, type, row ) {
                if (type == 'display' || type == 'filter') {
                    console.log("enabled");
                    console.log(data);
                    if (data==0) {
                        return "No";
                    }
                    else {
                        return "Sí";
                    }
                };
                return data;
            }},
            {
                data: null,
                searchable: false,
                render: function(data, type, row) {
                    var consultarBtn = '<a class="btn btn-small btn-secondary" href="'+"{{ URL::to('dashboard/users/') }}/"+row.id+'/edit">'+"{{ __('Editar') }}"+"</a>";
                    if (row.enabled) {
                        var enableBtn = '<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal" data-enabled="true" data-id="'+row.id+'" data-name="'+row.name+'">{{ __("Deshabilitar") }}</button>';
                    }
                    else {
                        var enableBtn = '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#deleteModal" data-enabled="false" data-id="'+row.id+'" data-name="'+row.name+'">{{ __("Habilitar") }}</button>';
                    }
                    return consultarBtn+enableBtn;
                }
            }
        ]
    });
});
</script>
@endsection
