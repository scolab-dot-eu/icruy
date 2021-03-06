@extends('layouts.dashboard')

@section('content')

    <h2>Roles</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <td>ID</td>
                <td>Name</td>
                <td>Desc</td>
            </tr>
        </thead>
        <tbody>
        @foreach($roles as $value)
            <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ $value->desc }}</td>
                <td>
                    <a class="btn btn-small btn-secondary" href="{{ URL::to('dashboard/roles/' . $value->id . '/edit') }}">Editar</a>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal" data-id="{{$value->id}}" data-name="{{$value->name}}">Borrar</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <a class="btn btn-small btn-info" href="{{ URL::to('dashboard/roles/create') }}">Nuevo rol</a>

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
              var action = '/dashboard/roles/' + id;
              $('#deleteForm').attr('action', action);
            });
        $('#confirmDeleteButton').on('click', function(event) {
            $('#deleteForm').submit();
        });
    });
</script>
@endsection
