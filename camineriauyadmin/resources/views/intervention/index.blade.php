@extends('layouts.dashboard')

@section('content')

    <h2>{{ __('Intervenciones') }}</h2>
    <table class="table table-striped table-bordered" id="dt-icr-index">
        <thead>
            <tr>
                <td>ID</td>
                <td>{{ __('Tipo elemento') }}</td>
                <td>{{ __('Fecha') }}</td>
                <td>{{ __('Dep.') }}</td>
                <td>{{ __('Estado') }}</td>
                <td>{{ __('Camino') }}</td>
                <td>{{ __('Tarea') }}</td>
                <td>{{ __('Monto') }}</td>
                <td></td>
            </tr>
        </thead>
    </table>
    @if (Auth::user()->isAdmin() || Auth::user()->isManager())
        <a class="btn btn-small btn-info" href="{{ URL::to('dashboard/interventions/create') }}">Nueva intervención</a>
    @endif
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
              var action = '/dashboard/interventions/' + id;
              $('#deleteForm').attr('action', action);
            });
        $('#confirmDeleteButton').on('click', function(event) {
            $('#deleteForm').submit();
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
                    return dateParts[2] + "/" + dateParts[1] + "/" + dateParts[0];
                }
            }
            return date;
        }
        return dateStr;
    };
    
    var theTable = $('#dt-icr-index').DataTable({
        language: dataTablesSpanishLang,
        processing: true,
        serverSide: true,
        dom: "<'row'<'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            "url": '{!! route('interventions.datatables') !!}',
            "data": function (d) {
                var re = new RegExp("/", "g");
                d.search.value = d.search.value.replace(re, " ");
                return d;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tipo_elem', name: 'tipo_elem' },
            { data: 'fecha_interv', name: 'fecha_interv', render:  formatDate},
            { data: 'departamento', name: 'departamento' },
            { data: 'status', name: 'status', render: function ( data, type, row ) {
                if (typeof data == "string") {
                    return data.substr(0, 14);
                }
                return "";
            }},
            { data: 'codigo_camino', name: 'codigo_camino' },
            { data: 'tarea', name: 'tarea' },
            { data: 'monto', name: 'monto' },
            {
                data: null,
                searchable: false,
                render: function ( data, type, row ) {
                    var consultarBtn = '<a class="btn btn-small btn-secondary" href="'+'{{ URL::to("dashboard/interventions/") }}/'+row.id+'/edit">Consultar</a>';
                    @if (Auth::user()->isAdmin() || Auth::user()->isManager())
                    var borrarBtn = '<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal" data-id="'+row.id+'" data-name="'+row.fecha_interv+' - '+row.codigo_camino+'">Borrar</button>';
                    @else
                    var borrarBtn = "";
                    @endif
                    return consultarBtn + borrarBtn;
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
