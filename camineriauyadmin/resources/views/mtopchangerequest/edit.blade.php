@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{ __('Actualizar petición de cambios MTOP') }}
            </h3>
            {{ Form::model( $mtopchangerequest, ['route' => ['mtopchangerequests.update', $mtopchangerequest->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('mtopchangerequest._fields')
                <br>
                <a href="{!! route('mtopchangerequests.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                <input type="submit" class="btn btn-info" name="action_comment" value="{{ __('Comentar') }}">
                @if ($mtopchangerequest->isOpen)
                    @if (Auth::user()->isMtopManager())
                    <input type="submit" class="btn btn-info" name="action_validate" value="{{ __('Validar') }}">
                    <input type="submit" class="btn btn-warning" name="action_reject" value="{{ __('Rechazar') }}">
                    @elseif (Auth::user()->id == $mtopchangerequest->requested_by_id)
                    <input type="submit" class="btn btn-warning" name="action_cancel" value="{{ __('Cancelar petición') }}">
                    @endif
                @endif
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

@section('custom_scripts')
    <script type="text/javascript">
    function createMap(div_id, feature, zoom, center=null) {
        var zoom = 12;
        var center;
        if (feature) {
            var layer = L.geoJSON(feature, {
                pointToLayer: function (feature, latlng) {
                    var myIcon = L.icon({
                        iconUrl: '/images/vendor/leaflet/dist/marker-icon.png',
                        iconSize:    [25, 41],
                        iconAnchor:  [12, 41],
                        popupAnchor: [1, -34],
                        tooltipAnchor: [16, -28],
                    });
                    var markerOptions = {
                        "icon": myIcon
                    };
                    return L.marker(latlng, markerOptions);
                }
            });
        }
        if (center==null && layer && layer.getBounds()) {
            center = layer.getBounds().getCenter();
        }
        var theMap = L.map(div_id, {"zoom": zoom, "center": center});
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(theMap);
        if (layer) {
            layer.addTo(theMap);
        }
        return theMap;
    }

    var proposedFeatStr = '{!! $mtopchangerequest->feature !!}';
    var previousFeatStr = '{!! $mtopchangerequest->feature_previous !!}';
    try {
        var previousFeat =  JSON.parse(previousFeatStr);
    }
    catch(e) {
        var previousFeat = null;
    }
    
    var operation = '{{ $mtopchangerequest->operation }}';
    var zoom = 12;
    if (operation == 'create') {
        var proposedFeatMap = createMap('map-proposed-feat', JSON.parse(proposedFeatStr), zoom);
        var previousFeatMap = createMap('map-previous-feat', null, zoom, proposedFeatMap.getCenter());
    }
    else {
        if (operation == 'update') {
            var proposedFeatMap = createMap('map-proposed-feat', JSON.parse(proposedFeatStr), zoom);
        }

        var currentFeatureUrl = '{!! $currentFeatureUrl !!}';
        $.ajax({
            url: currentFeatureUrl,
            async: true
        }).done(function(existingFeature) {
            $('#map-previous-feat').empty();
            if (operation == 'delete') {
                var previousFeatMap = createMap('map-previous-feat', existingFeature, zoom);
                var proposedFeatMap = createMap('map-proposed-feat', null, zoom, previousFeatMap.getCenter());
            }
            else { // update
                var previousFeatMap = createMap('map-previous-feat', existingFeature, zoom);
            }
        }).fail(function(error) {
            console.log( "Error al cargar configuración" );
            $('#map-previous-feat').text('El geoservicio de Caminería no está disponible. Inténtelo de nuevo más tarde.');
        }).always(function (resp, textStatus, xhr) {
            if(xhr.status != 200) {
                console.log( "Error al cargar configuración" );
                $('#map-previous-feat').text('El geoservicio de Caminería no está disponible. Inténtelo de nuevo más tarde.');
            }
        });
    }
    </script>
@endsection