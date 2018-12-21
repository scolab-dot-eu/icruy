            <div class="form-group">
                {!! Form::label('name', 'Nombre') !!}
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('title', 'Título') !!}
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('protocol', 'Protocolo') !!}
                {!! Form::select('protocol', ['wms' => 'WMS', 'tilelayer' => 'Teselas', 'bing' => 'Bing', 'wfs' => 'WFS', 'empty' => 'Capa vacía']); !!}
            </div>
            <div class="form-group">
                {!! Form::label('url', 'URL') !!}
                {!! Form::text('url', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('api_key', 'API KEY') !!}
                {!! Form::text('api_key', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('isbaselayer', $supportlayerdef->isbaselayer, null, ['id' => 'isbaselayer', 'class' => 'form-check-input']) !!}
                {!! Form::label('isbaselayer', __('Es capa base')) !!}
            </div>
            <div class="form-check">
                {!! Form::checkbox('visible', '1', null, ['id' => 'visible', 'class' => 'form-check-input']) !!}
                {!! Form::label('visible', __('Visible por defecto')) !!}
            </div>
            <div class="form-group">
                {!! Form::label('layergroup', 'Grupo de capas') !!}
                {!! Form::select('layergroup', ['Capas de apoyo' => 'Capas de apoyo', 'Capas base' => 'Capas base', 'Caminería MTOP' => 'Caminería MTOP']); !!}
            </div>
            <div class="form-group">
                {!! Form::label('conf', 'Configuración extra') !!}
                {!! Form::textarea('conf', null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group">
            </div>
