@component('mail::message')
# Inventario de Caminería Rural


Se ha creado una [petición de cambios MTOP]({{ $changeRequestUrl }}).
Se ha adjuntado la geometría propuesta en formato GeoJSON.

Recuerde acceder de nuevo a la petición para validarla o rechazarla una vez procesados los cambios.
Para ello, puede utilizar el siguiente enlace: [{{ $changeRequestUrl }}]({{ $changeRequestUrl }}).

Saludos,  
{{ config('app.name') }}
@endcomponent
