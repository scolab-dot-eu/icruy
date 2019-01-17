        <div class="container">
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('departamento', __('Departamento')) !!}
                    {!! Form::select('departamento', $user_departments, null, ['class' => 'form-control es-input', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('id', __('Identificador')) !!}
                    {!! Form::text('id', null, ['readonly' => '', 'class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    {!! Form::label('anyo_interv', __('Año intervención')) !!}
                    {!! Form::number('anyo_interv', null, ['class' => 'form-control', 'required' => true]) !!}
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
            <div class="col-12">
                <div class="form-group">
                    {!! Form::label('tarea', __('Tarea')) !!}
                    <!-- tarea es una lista abierta, así que se complica un poco.
                         Usamos un plugin de jquery y un poco de pegamento -->
                    <!-- FIXME: Debería ir a algún fichero de configuración -->
                    {!! Form::select('tarea', [
                        'ME' => 'ME: MANTENIMIENTO EXTRAORDINARIO',
                        'ME+AP+OA'=> 'ME+AP+OA: MANTENIMIENTO EXTRAORDINARIO+APORTE+OBRA DE ARTE',
                        'ME+OA'=> 'ME+OA: MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE',
                        'MO'=> 'MO: MANTENIMIENTO ORDINARIO',
                        'MO+AP'=> 'MO+AP: MANTENIMIENTO+ORDINARIO+APORTE',
                        'MO+AP+OA'=> 'MO+AP+O: MANTENIMIENTO ORDINARIO+APORTE+OBRA DE ARTE',
                        'MO+ME'=> 'MO+ME: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO',
                        'MO+ME+OA'=> 'MO+ME+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE',
                        'MO+MF'=> 'MO+MF:MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA',
                        'MO+MF+AP'=> 'MO+MF+AP: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE',
                        'MO+MF+AP+OA'=> 'MO+MF+AP+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE+OBRA DE ARTE',
                        'MO+OA'=> 'MO+OA: MANTENIMIENTO ORDINARIO+OBRA DE ARTE',
                        'OA'=> 'OA: OBRA DE ARTE',
                        'PE'=> 'PE: PERFILADO',
                        'PE+AP'=> 'PE+AP: PERFILADO+APORTE',
                        'TBS'=> 'TBS: TRATAMIENTO BITUMINOSO SIMPLE',
                        'TBD'=> 'TBD: TRATAMIENTO BITUMINOSO DOBLE',
                        'TBD/S'=> 'TBD/S: TRATAMIENTO BITUMINOSO DOBLE CON SELLADO',
                        'ImpR'=> 'ImpR: IMPRIMACIÓN'
                    ], null, ['class' => 'form-control es-input', 'hidden'=>true, 'required'=>true]) !!}
                    {!! Form::select('tarea_es', [
                        'ME' => 'ME: MANTENIMIENTO EXTRAORDINARIO',
                        'ME+AP+OA'=> 'ME+AP+OA: MANTENIMIENTO EXTRAORDINARIO+APORTE+OBRA DE ARTE',
                        'ME+OA'=> 'ME+OA: MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE',
                        'MO'=> 'MO: MANTENIMIENTO ORDINARIO',
                        'MO+AP'=> 'MO+AP: MANTENIMIENTO+ORDINARIO+APORTE',
                        'MO+AP+OA'=> 'MO+AP+O: MANTENIMIENTO ORDINARIO+APORTE+OBRA DE ARTE',
                        'MO+ME'=> 'MO+ME: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO',
                        'MO+ME+OA'=> 'MO+ME+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO EXTRAORDINARIO+OBRA DE ARTE',
                        'MO+MF'=> 'MO+MF:MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA',
                        'MO+MF+AP'=> 'MO+MF+AP: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE',
                        'MO+MF+AP+OA'=> 'MO+MF+AP+OA: MANTENIMIENTO ORDINARIO+MANTENIMIENTO DE FAJA+APORTE+OBRA DE ARTE',
                        'MO+OA'=> 'MO+OA: MANTENIMIENTO ORDINARIO+OBRA DE ARTE',
                        'OA'=> 'OA: OBRA DE ARTE',
                        'PE'=> 'PE: PERFILADO',
                        'PE+AP'=> 'PE+AP: PERFILADO+APORTE',
                        'TBS'=> 'TBS: TRATAMIENTO BITUMINOSO SIMPLE',
                        'TBD'=> 'TBD: TRATAMIENTO BITUMINOSO DOBLE',
                        'TBD/S'=> 'TBD/S: TRATAMIENTO BITUMINOSO DOBLE CON SELLADO',
                        'ImpR'=> 'ImpR: IMPRIMACIÓN'
                    ], $intervention->tarea, ['class' => 'form-control es-input', 'id'=>'tarea_es', 'required'=>true]) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('longitud', __('Longitud')) !!}
                    {!! Form::number('longitud', null, ['class' => 'form-control', 'step' => '0.01', 'max' => '9.99']) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('financiacion', __('Financiación')) !!}
                    {!! Form::select('financiacion', ['IND'=>'INTENDENCIA DEPARTAMENTAL', 'OPP'=>'OPP', 'PRI'=> 'PRIVADA', 'OTR' => 'OTROS'], null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="form-group">
                    {!! Form::label('forma_ejecucion', __('Forma ejecución')) !!}
                    {!! Form::select('forma_ejecucion', ['ADM'=>'ADMINISTRACIÓN', 'CON'=>'CONTRATO', 'MIX'=> 'MIXTA'], null, ['class' => 'form-control es-input', 'required'=>true]) !!}
                </div>
            </div>
          </div>
        </div>