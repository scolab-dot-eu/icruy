        <div class="container">
          <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('layer', __('Tabla')) !!}
                    {!! Form::text('layer', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('operation', __('Operación')) !!}
                    {!! Form::text('operation', $changerequest->operationLabel, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status', __('Estado')) !!}
                    {!! Form::text('status', $changerequest->statusLabel, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-7">
                <div class="form-group">
                    {!! Form::label('author[email]', __('Autor')) !!}
                    {!! Form::text('author[email]', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    {!! Form::label('created_at', __('Fecha solicitud')) !!}
                    {!! Form::text('created_at', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-7">
                <div class="form-group">
                    {!! Form::label('validator[email]', __('Revisada por')) !!}
                    {!! Form::text('validator[email]', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    {!! Form::label('updated_at', __('Fecha actualización')) !!}
                    {!! Form::text('updated_at', null, ['readonly' => '', 'class' => 'form-control']) !!}
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
          <div class="row">
            <div class="col">
                @if ($previousFeature!==null)
                    @foreach($previousFeature->properties as $key => $value)
                        <span style="text-transform: uppercase; color: #e0a800">{{ $key }}:</span> {{ $value }} <br> 
                    @endforeach
                @endif
            </div>
            <div class="col">
                @if ($proposedFeature!==null)
                    @foreach($proposedFeature->properties as $key => $value)
                        <span style="text-transform: uppercase; color: #e0a800">{{ $key }}:</span> {{ $value }} <br> 
                    @endforeach
                @endif
            </div>
          </div>
        </div>