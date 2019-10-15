@component('mail::message')
# Inventario de Caminería Rural


Se ha creado una [petición de cambios MTOP]({{ $changeRequestUrl }}).
Se ha adjuntado la geometría propuesta en formato GeoJSON.

Puede acceder a la petición utlizando el siguiente enlace: [{{ $changeRequestUrl }}]({{ $changeRequestUrl }}), donde pondrá consultar o cancelar la petición.

Saludos,  
{{ config('app.name') }}
@endcomponent
