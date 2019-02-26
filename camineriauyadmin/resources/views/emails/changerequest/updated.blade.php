@component('mail::message')
# Inventario de Caminería Rural

Se ha actualizado su [petición de cambios]({{ $changeRequestUrl }}).

Estado: {{ $status }}  
Tipo de elemento: {{ $layer }}  
Departamento: {{ $departamento }}  
Operación: {{ $operation }}  

Puede usar el siguiente enlace para [acceder a la petición de cambios]({{ $changeRequestUrl }}), donde podrá validar o rechazar la petición:
[{{ $changeRequestUrl }}]({{ $changeRequestUrl }}).

Saludos,  
{{ config('app.name') }}
@endcomponent
