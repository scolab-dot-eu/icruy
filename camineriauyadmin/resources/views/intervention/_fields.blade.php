
          <div class="row">
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('departamento', __('Departamento')) !!}
                    @if ($editable)
                        {!! Form::select('departamento', $user_departments, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                    @else
                        {!! Form::select('departamento', $user_departments, null, ['readonly' => '', 'class' => 'form-control es-input', 'required' => true]) !!}
                    @endif
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
                    @if ($editable)
                        {!! Form::date('fecha_interv', null, ['class' => 'form-control', 'required' => true]) !!}
                    @else
                        {!! Form::date('fecha_interv', null, ['readonly' => '', 'class' => 'form-control', 'required' => true]) !!}
                    @endif
                    <!-- {!! Form::number('anyo_interv', null, ['class' => 'form-control', 'required' => true]) !!}
                     -->
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('codigo_camino', __('Código camino')) !!}
                    @if ($editable)
                        {!! Form::text('codigo_camino', null, ['class' => 'form-control']) !!}
                    @else
                        {!! Form::text('codigo_camino', null, ['readonly' => '', 'class' => 'form-control']) !!}
                    @endif
                    
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('tipo_elem', __('Tipo elemento')) !!}
                    @if ($editable)
                        {!! Form::select('tipo_elem', $inventory_layers, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                    @else
                        {!! Form::select('tipo_elem', $inventory_layers, null, ['readonly' => '', 'class' => 'form-control es-input', 'required' => true]) !!}
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('id_elem', __('Código elemento')) !!}
                    @if ($editable)
                        {!! Form::number('id_elem', null, ['class' => 'form-control']) !!}
                    @else
                        {!! Form::number('id_elem', null, ['readonly' => '', 'class' => 'form-control']) !!}
                    @endif
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('monto', __('Monto')) !!}
                    @if ($editable)
                        {!! Form::number('monto', null, ['class' => 'form-control', 'step' => '0.01', 'max' => '9999999999.99']) !!}
                    @else
                        {!! Form::number('monto', null, ['readonly' => '', 'class' => 'form-control', 'step' => '0.01', 'max' => '9999999999.99']) !!}
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('longitud', __('Longitud (km)')) !!}
                    @if ($editable)
                        {!! Form::number('longitud', null, ['class' => 'form-control', 'step' => '0.01', 'max' => '999.99']) !!}
                    @else
                        {!! Form::number('longitud', null, ['readonly' => '', 'class' => 'form-control', 'step' => '0.01', 'max' => '999.99']) !!}
                    @endif
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('tarea', __('Tarea')) !!}
                    @if ($editable)
                        {!! Form::select('tarea', $tareaSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                    @else
                        {!! Form::select('tarea', $tareaSelect, null, ['readonly' => '', 'class' => 'form-control es-input', 'required'=>true]) !!}
                    @endif
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('financiacion', __('Financiación')) !!}
                    @if ($editable)
                        {!! Form::select('financiacion', $financiacionSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                    @else
                        {!! Form::select('financiacion', $financiacionSelect, null, ['readonly' => '', 'class' => 'form-control es-input', 'required'=>true]) !!}
                    @endif
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('forma_ejecucion', __('Forma ejecución')) !!}
                    @if ($editable)
                        {!! Form::select('forma_ejecucion', $formaEjecucionSelect, null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                    @else
                        {!! Form::select('forma_ejecucion', $formaEjecucionSelect, null, ['readonly' => '', 'class' => 'form-control es-input', 'required'=>true]) !!}
                    @endif
                </div>
            </div>
          </div>