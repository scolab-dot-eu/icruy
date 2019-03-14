
          <div class="row">
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('departamento', __('Departamento')) !!}
                    {!! Form::select('departamento', $user_departments, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('id', __('Identificador')) !!}
                    {!! Form::text('id', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('status', __('Estado')) !!}
                    {!! Form::text('status', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('fecha_interv', __('Fecha intervención')) !!}
                    {!! Form::date('fecha_interv', null, ['class' => 'form-control', 'required' => true]) !!}
                    <!-- {!! Form::number('anyo_interv', null, ['class' => 'form-control', 'required' => true]) !!}
                     -->
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('codigo_camino', __('Código camino')) !!}
                    {!! Form::text('codigo_camino', null, ['class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('tipo_elem', __('Tipo elemento')) !!}
                    {!! Form::select('tipo_elem', $inventory_layers, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('id_elem', __('Código elemento')) !!}
                    {!! Form::number('id_elem', null, ['class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('monto', __('Monto')) !!}
                    {!! Form::number('monto', null, ['class' => 'form-control', 'step' => '0.01', 'max' => '9999999999.99']) !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('longitud', __('Longitud (km)')) !!}
                    {!! Form::number('longitud', null, ['class' => 'form-control', 'step' => '0.01', 'max' => '9.99']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('tarea', __('Tarea')) !!}
                    {!! Form::select('tarea', $tareaSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('financiacion', __('Financiación')) !!}
                    {!! Form::select('financiacion', $financiacionSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('forma_ejecucion', __('Forma ejecución')) !!}
                    {!! Form::select('forma_ejecucion', $formaEjecucionSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                </div>
            </div>
          </div>