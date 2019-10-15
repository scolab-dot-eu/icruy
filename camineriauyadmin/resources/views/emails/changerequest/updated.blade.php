@component('mail::message')
# Inventario de Caminería Rural

Se ha actualizado la [petición de cambios \[\#{{ $changeRequestId }}\]]({{ $changeRequestUrl }}).
@if ($newComment)

El siguiente comentario se ha añadido:

*{{$newComment}}*
@endif

Estado: {{ $status }}  
Tipo de elemento: {{ $layer }}  
Departamento: {{ $departamento }}  
Operación: {{ $operation }}  

Puede usar el siguiente enlace para [acceder a la petición de cambios]({{ $changeRequestUrl }}):
[{{ $changeRequestUrl }}]({{ $changeRequestUrl }}).

Saludos,  
{{ config('app.name') }}
@endcomponent
