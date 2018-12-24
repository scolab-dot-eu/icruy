@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{ __('Actualizar petición de cambios') }}
            </h3>
            {{ Form::model( $changerequest, ['route' => ['changerequests.update', $changerequest->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('changerequest._fields')
                @if ($changerequest->isClosed)
                <a href="{!! route('changerequests.index') !!}" role="button" class="btn btn-info">{{ __('Cerrar') }}</a>
                @else
                <input type="submit" class="btn btn-info" name="action_validate" value="{{ __('Validar') }}">
                <input type="submit" class="btn btn-warning" name="action_reject" value="{{ __('Rechazar') }}">
                @endif
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

@section('custom_scripts')
    <script type="text/javascript">
    function createMap(div_id, feature) {
        var zoom = 11;
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
        if (layer && layer.getBounds()) {
            center = layer.getBounds().getCenter();
        }
        else {
            center = L.latLng(-32.720750, -55.910880);
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

    var proposedFeatStr = '{!! $changerequest->feature !!}';
    var proposedFeatMap = createMap('map-proposed-feat', JSON.parse(proposedFeatStr));
    
    var previousFeatStr = '{!! $changerequest->feature_previous !!}';
    try {
        var previousFeat = JSON.parse(previousFeatStr);
    } catch (error) {
        console.log(error);
        var previousFeat = null;
    }
    var previousFeatMap = createMap('map-previous-feat', previousFeat);
    if (!previousFeat) {
        previousFeatMap.setView(proposedFeatMap.getCenter(), proposedFeatMap.getZoom());
    }



    /*
    var previousFeatMap = L.map('map-previous-feat').setView([-32.311509,-54.438629], 8);
    var proposedFeatMap = L.map('map-proposed-feat').setView([-32.111659, -54.089813], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(previousFeatMap);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(proposedFeatMap);
    
    var marker1 = L.marker([-32.311509, -54.438629]).addTo(previousFeatMap);
    var marker2 = L.marker([-32.111659, -54.089813]).addTo(proposedFeatMap);
    */
    </script>
@endsection