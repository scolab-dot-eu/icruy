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
                    <label for="author_desc">Autor</label>
                    @if ($changerequest->author)
                        <input readonly class="form-control" name="author_desc" type="text" value="{{ $changerequest->author->name }} ({{ $changerequest->author->email }})" id="author_desc">
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
                    @if ($changerequest->validator)
                        <input readonly class="form-control" name="validator_desc" type="text" value="{{ $changerequest->validator->name }} ({{ $changerequest->validator->email }})" id="validator_desc">
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
          <div class="row">
            <div class="col">
                @if ($previousFeature!==null)
                    @foreach($previousFeature->properties as $key => $value)
                        @if ($key!='origin' && $key!='status' && $key!='created_at' && $key!='updated_at')
                        <span style="text-transform: uppercase; color: #e0a800">{{ $key }}:</span> {{ $value }} <br>
                        @endif 
                    @endforeach
                @endif
            </div>
            <div class="col">
                @if ($proposedFeature!==null)
                    @foreach($proposedFeature->properties as $key => $value)
                        @if ($key!='origin' && $key!='status' && $key!='created_at' && $key!='updated_at')
                        <span style="text-transform: uppercase; color: #e0a800">{{ $key }}:</span> {{ $value }} <br>
                        @endif 
                    @endforeach
                @endif
            </div>
          </div>
          <div class="row">
            <div class="col">
                <h4 id="theComments">Comentarios:</h4>
                @foreach($comments as $comment)
                    Autor: {{ $comment->user->name }} ({{ $comment->user->email }}) - Añadido: {{ $comment->createdAtFormatted }}
                    <div class="alert alert-primary" role="alert">
                    {{ $comment->message }}
                    </div> 
                @endforeach
            </div>
          </div>
          <div class="row">
            <div class="col">
                    {!! Form::label('newcomment', __('Añadir comentario')) !!}
                    {!! Form::textarea('newcomment', null, ['class' => 'form-control', 'rows' => 4]) !!}
            </div>
          </div>
        </div>