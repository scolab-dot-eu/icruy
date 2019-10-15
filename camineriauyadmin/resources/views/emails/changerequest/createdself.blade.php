@component('mail::message')
# Inventario de Caminería Rural

Se ha creado una nueva [petición de cambios]({{ $changeRequestUrl }}) que será revisada por un administrador.

Tipo de elemento: {{ $layer }}  
Departamento: {{ $departamento }}  
Operación: {{ $operation }}  

Puede usar el siguiente enlace para [acceder a la petición de cambios]({{ $changeRequestUrl }}), donde podrá consultar o cancelar su petición:
[{{ $changeRequestUrl }}]({{ $changeRequestUrl }}).

Saludos,  
{{ config('app.name') }}
@endcomponent
