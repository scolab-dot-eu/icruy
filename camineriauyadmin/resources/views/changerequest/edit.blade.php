@extends('layouts.dashboard')

@section('content')

    <div class="row" style="margin-top: 25px;">

        <div class="col">

            <h3>
                {{ __('Actualizar petición de cambios') }}
            </h3>
            {{ Form::model( $changerequest, ['route' => ['changerequests.update', $changerequest->id], 'method' => 'put', 'role' => 'form'] ) }}
                @include('changerequest._fields')
                <br>
                <a href="{!! route('changerequests.index') !!}" role="button" class="btn btn-info">{{ __('Volver') }}</a>
                <input type="submit" class="btn btn-info" name="action_comment" value="{{ __('Comentar') }}">
                @if ($changerequest->isOpen)
                    @if (Auth::user()->isAdmin())
                    <input type="submit" class="btn btn-info" name="action_validate" value="{{ __('Validar') }}">
                    <input type="submit" class="btn btn-warning" name="action_reject" value="{{ __('Rechazar') }}">
                    @else
                    <input type="submit" class="btn btn-warning" name="action_cancel" value="{{ __('Cancelar petición') }}">
                    @endif
                @endif
            {{ Form::close() }}
            <br />
        </div>

    </div>

@endsection

@section('custom_scripts')
    @if (isset($previousFeature->geometry) || isset($proposedFeature->geometry))
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

    var proposedFeatStr = '{!! $changerequest->feature !!}';
    var previousFeatStr = '{!! $changerequest->feature_previous !!}';

    var operation = '{{ $changerequest->operation }}';
    var zoom = 12;
    if (operation == 'create') {
        var proposedFeatMap = createMap('map-proposed-feat', JSON.parse(proposedFeatStr), zoom);
        var previousFeatMap = createMap('map-previous-feat', null, zoom, proposedFeatMap.getCenter());
    }
    else if (operation == 'delete') {
        var previousFeatMap = createMap('map-previous-feat', JSON.parse(previousFeatStr), zoom);
        var proposedFeatMap = createMap('map-proposed-feat', null, zoom, previousFeatMap.getCenter());
        
    }
    else {
        var previousFeatMap = createMap('map-previous-feat', JSON.parse(previousFeatStr), zoom);
        var proposedFeatMap = createMap('map-proposed-feat', JSON.parse(proposedFeatStr), zoom);
    }
    </script>
    @endif
@endsection