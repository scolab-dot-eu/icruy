        <div class="container">
          <div class="row">
            <div class="col">
                <div class="form-group">
                    {!! Form::label('email', __('Email')) !!}
                    {!! Form::email('email', null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('password', __('Contraseña')) !!}
                    <!--  workarounds for password autocomplete -->
                    {!! Form::email('not-an-email', null, ['class' => 'form-control hidden d-none', 'autocomplete' => 'new-password']) !!}
                    <input name="not-a-password" type="password" value="" style="display: none">
                    <!--  end workarounds for password autocomplete -->
                    {!! Form::password('password', null, ['class' => 'form-control', 'autocomplete' => 'new-password']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('name', __('Nombre')) !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('phone', __('Teléfono')) !!}
                    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
                {{__('Permisos')}}
            </div>
          </div>
          <div class="row">
            <div class="col">
                <div class="form-group">
                    <div class="form-check">
                        {!! Form::checkbox('roleadmin', null, $roleadmin, ['id' => 'roleadmin', 'class' => 'form-check-input']) !!}
                        {!! Form::label('roleadmin', __('Administrador')) !!}
                      <!-- <input class="form-check-input" type="checkbox" name="roleadmin" id="roleadmin">
                      <label class="form-check-label" for="roleadmin">
                        {{__('Administrador')}}
                      </label>  -->
                    </div>
                    <div class="form-check">
                        {!! Form::checkbox('rolemanager', null, $rolemanager, ['id' => 'rolemanager', 'class' => 'form-check-input']) !!}
                        {!! Form::label('rolemanager', __('Gestor departamental')) !!}
                    </div>
                    <div class="form-check">
                        {!! Form::checkbox('rolemtopmanager', null, $rolemtopmanager, ['id' => 'rolemtopmanager', 'class' => 'form-check-input']) !!}
                        {!! Form::label('rolemtopmanager', __('Gestor MTOP')) !!}
                    </div>
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
                {{__('Departamentos')}}
            </div>
          </div>
          <div class="row">
            <div class="col">
               @foreach($all_departments as $dep)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="on" name="departments[{{$dep->code}}]" id="departments.{{$dep->code}}"
                    @if ($user->departments->contains($dep)) checked @endif >
                  <label class="form-check-label" for="departments.{{$dep->code}}">
                    {{$dep->code}} - {{$dep->name}} 
                  </label>
                </div>
                @endforeach
            </div>
          </div>
        </div>