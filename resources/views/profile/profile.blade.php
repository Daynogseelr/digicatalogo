@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'profile'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card" style="height: 700px !important;">
                    <div class="card-header">
                        <h5 class="title">{{ __('Editar usuario') }}</h5>
                    </div>
                    <form method="post" action="{{ route('updateProfile') }}" autocomplete="off">
                        <div class="card-body">
                            <div class="row">
                                @csrf
                                @method('post')
                                @if (auth()->user()->type == 'EMPRESA')
                                    <div class="col-md-12">
                                @else
                                    <div class="col-md-6">
                                @endif
                                    <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                        <label>{{ __('Nombre') }}</label>
                                        <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="{{ __('Nombre del usuario') }}" value="{{ old('name', auth()->user()->name) }}" minlength="2" maxlength="20" required onkeypress="return sololetras(event)" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'name'])
                                    </div>
                                </div>
                                @if (auth()->user()->type != 'EMPRESA')
                                    <div class="col-md-6">
                                        <div class="form-group{{ $errors->has('last_name') ? ' has-danger' : '' }}">
                                            <label>{{ __('Apellido') }}</label>
                                            <input type="text" name="last_name" class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}" placeholder="{{ __('Apellido del usuario') }}" value="{{ old('last_name', auth()->user()->last_name) }}" minlength="2" maxlength="20" required onkeypress="return sololetras(event)" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                            @include('alerts.feedback', ['field' => 'last_name'])
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('ci') ? ' has-danger' : '' }}">
                                    @if (auth()->user()->type == 'EMPRESA')
                                        <label>RIF (*):</label>
                                    @else
                                        <label>Cédula (*):</label>
                                    @endif
                                        <div class="row">
                                            <div class="col-sm-4" style="padding-right:0">
                                                <select class="form-select required" name="nationality" >
                                                    <option {{ auth()->user()->nationality == "V" ? 'selected' : ''}} value="V">V</option>
                                                    <option {{ auth()->user()->nationality == "E" ? 'selected' : ''}} value="E">E</option>	
                                                    <option {{ auth()->user()->nationality == "J" ? 'selected' : ''}} value="J">J</option>	
                                                </select>	
                                            </div>
                                            <div class="col-sm-8" style="padding-left:0">
                                                <input name="ci" type="text" class="form-control{{ $errors->has('ci') ? ' is-invalid' : '' }}" value="{{ old('ci', auth()->user()->ci) }}" id="ci" placeholder="Documento de identidad" title="Es obligatorio un documento"  minlength="7" maxlength="9" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">     
                                            </div>
                                        </div>
                                        @include('alerts.feedback', ['field' => 'ci'])
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('phone') ? ' has-danger' : '' }}">
                                        <label >Teléfono (*):</label>
                                        <input name="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone', auth()->user()->phone) }}" id="phone" placeholder="Teléfono del usuario" title="Es obligatorio un telefono"  minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'phone'])
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group{{ $errors->has('direction') ? ' has-danger' : '' }}">
                                        <label >Direccion (*):</label>
                                        <input name="direction" type="text" class="form-control{{ $errors->has('direction') ? ' is-invalid' : '' }}" value="{{ old('direction', auth()->user()->direction) }}" id="direction" title="Es obligatorio una direccion" minlength="5" maxlength="100" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'direction'])
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                                        <label>{{ __('Correo Electronico') }}</label>
                                        <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="{{ __('Correo Electronico') }}" value="{{ old('email', auth()->user()->email) }}" minlength="5" maxlength="40" required  onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'email'])
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-fill btn-primary">{{ __('Guardar') }}</button>
                        </div>
                    </form>
                    <div class="card-footer">
                        @if (auth()->user()->type == 'EMPRESA')
                            <form method="post" action="{{ route('updateProfileFile') }}" autocomplete="off" enctype="multipart/form-data">
                                @csrf
                                @method('post')
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <label class="form-label" for="form2Example17">Imagen de la Empresa</label>
                                    <input name="logo" type="file" accept="image/jpg,image/jpeg,image/png" class="form-control" id="logo"  title="Es obligatorio una Imagen">
                                    @include('alerts.feedback', ['field' => 'logo'])
                                    @if ($errors->has('logo'))
                                        {{$errors->first('logo')}}
                                    @endif
                                </div>
                                <br>
                                <button type="submit" class="btn btn-fill btn-primary">{{ __('Guardar Imagen') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="height: 700px !important;">
                    <div class="card-header">
                        <h5 class="title">{{ __('Contraseña') }}</h5>
                    </div>
                    <form method="post" action="{{ route('passwordProfile') }}" autocomplete="off">
                        <div class="card-body">
                            @csrf
                            @method('post')
                            @include('alerts.success', ['key' => 'password_status'])
                            <div class="form-group{{ $errors->has('old_password') ? ' has-danger' : '' }}">
                                <label>{{ __('Introduce contraseña') }}</label>
                                <input type="password" name="old_password" class="form-control{{ $errors->has('old_password') ? ' is-invalid' : '' }}" placeholder="{{ __('Introduce contraseña') }}" value="{{ old('old_password')}}" required>
                                @include('alerts.feedback', ['field' => 'old_password'])
                            </div>
                            <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                                <label>{{ __('Nueva contraseña') }}</label>
                                <input type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ __('Nueva contraseña') }}" value="{{ old('password')}}" required>
                                @include('alerts.feedback', ['field' => 'password'])
                            </div>
                            <div class="form-group">
                                <label>{{ __('Confirma nueva contraseña') }}</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirma nueva contraseña') }}" value="{{ old('password_confirmation')}}" required>
                            </div>
                            <button type="submit" class="btn btn-fill btn-primary">{{ __('Cambiar contraseña') }}</button>
                        </div>
                        <div class="card-footer">
                           
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @if (session($key ?? 'status'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Registro Actualizado',
                showConfirmButton: false,
                timer: 1500
            })
            
        </script>
    @endif  
    @if (session($key ?? 'yes_password_status'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Registro Actualizado',
                showConfirmButton: false,
                timer: 1500
            })
            password_status
        </script>
    @endif 
@endsection
