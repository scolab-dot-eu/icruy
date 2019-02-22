        <div class="container">
          <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('operation', __('Operación')) !!}
                    {!! Form::text('operation', $mtopchangerequest->operationLabel, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('status', __('Estado')) !!}
                    {!! Form::text('status', $mtopchangerequest->statusLabel, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-7">
                <div class="form-group">
                    <label for="author_desc">Autor</label>
                    @if ($mtopchangerequest->author)
                        <input readonly class="form-control" name="author_desc" type="text" value="{{ $mtopchangerequest->author->name }} ({{ $mtopchangerequest->author->email }})" id="author_desc">
                    @else
                        <input readonly class="form-control" name="author_desc" type="text" value="" id="author_desc">
                    @endif
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    {!! Form::label('createdAtFormatted', __('Fecha solicitud')) !!}
                    {!! Form::text('createdAtFormatted', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-7">
                <div class="form-group">
                    <label for="validator_desc">Revisada por</label>
                    @if ($mtopchangerequest->validator)
                        <input readonly class="form-control" name="validator_desc" type="text" value="{{ $mtopchangerequest->validator->name }} ({{ $mtopchangerequest->validator->email }})" id="validator_desc">
                    @else
                        <input readonly class="form-control" name="validator_desc" type="text" value="" id="validator_desc">
                    @endif
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    {!! Form::label('updatedAtFormatted', __('Fecha actualización')) !!}
                    {!! Form::text('updatedAtFormatted', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-7">
                <div class="form-group">
                    {!! Form::label('codigo_camino', __('Código de camino')) !!}
                    {!! Form::text('codigo_camino', $mtopchangerequest->codigo_camino, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    {!! Form::label('feature_id', __('GID tramo')) !!}
                    {!! Form::text('feature_id', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                     <a href="{!! route('mtopchangerequests.feature', ['id'=>$mtopchangerequest->id, 'codigo_camino'=>$mtopchangerequest->codigo_camino]) !!}" download role="button" class="btn btn-info">{{ __('Descargar GeoJSON') }}</a>
                </div>
            </div>
          </div>
          <!-- 
          <div class="row">
            <div class="col">
                <div class="form-group">
                    {!! Form::label('feature', __('Feature')) !!}
                    {!! Form::textarea('feature', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div> -->
          <div class="row">
            <div class="col">
                Elemento previo:
            </div>
            <div class="col">
                Elemento propuesto:
            </div>
          </div>
          @if (isset($previousFeature->geometry) || isset($proposedFeature->geometry))
          <div class="row">
            <div class="col">
                <div class="dashboard-map" id="map-previous-feat">
                </div>
            </div>
            <div class="col">
                <div  class="dashboard-map" id="map-proposed-feat">
                </div>
            </div>
          </div>
          @endif
        </div>