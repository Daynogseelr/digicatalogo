@extends('app', ['class' => 'login-page', 'page' => __(''), 'contentClass' => 'login-page', 'pageSlug' => 'register'])
@section ('content')
    <section class="vh-100" style="background-color:  #ffffffd2;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel"  style="border-radius: 1rem 0 0 1rem; height:100% !important;">
                                    <div class="carousel-indicators">
                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                        @php
                                            $im = 0;
                                        @endphp
                                        @foreach ($companies as $company)
                                            @php
                                                $im++;
                                            @endphp
                                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$im}}" aria-label="Slide {{$im}}"></button>
                                        @endforeach
                                    </div>
                                    <div class="carousel-inner">
                                        <div class="carousel-item  active" data-bs-interval="3000">
                                            <img src="../logos/digicatalogo.jpeg" class="d-block w-100" alt="..."  style="border-radius: 1rem 0 0 1rem; height:100% !important;">
                                        </div>
                                        @foreach ($companies as $company)
                                            <div class="carousel-item"  data-bs-interval="2000">
                                                <img src="../logos/{{$company->logo}}" class="d-block w-100" alt="..."  style="border-radius: 1rem 0 0 1rem; height:100% !important;">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                      <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                      <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                      <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form method="post" action="{{ route('registerClient') }}" autocomplete="off">
                                        @csrf
                                        @method('post')

                                        @include('alerts.success')
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/img/DIGI.png"  style=" padding:0px; width: 50% !important; height: 190px !important; margin:auto !important;  margin-bottom: -50px !important; margin-top: -50px !important;" >
                                        </div>
                                        <h5 class="fw-normal " style="letter-spacing: 1px;">Registro</h5>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 form-outline mb-2 {{ $errors->has('name') ? ' has-danger' : '' }}">
                                                <input name="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" value="{{ old('name') }}" placeholder="Nombre" title="Es obligatorio un nombre" minlength="2" maxlength="20" required onkeypress="return sololetras(event)" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                                <label class="form-label" for="form2Example17">Nombre</label>
                                                @include('alerts.feedback', ['field' => 'name'])
                                            </div>
                                            <div class="col-md-6 col-sm-12 form-outline mb-2 {{ $errors->has('last_name') ? ' has-danger' : '' }}">
                                                <input name="last_name" type="text" class="form-control {{ $errors->has('last_name') ? ' is-invalid' : '' }}" value="{{ old('last_name') }}" id="last_name"  placeholder="Apellido" title="Es obligatorio un apellido" minlength="2" maxlength="20" required onkeypress="return sololetras(event)" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                                <label class="form-label" for="form2Example17">Apellido</label>
                                                @include('alerts.feedback', ['field' => 'last_name'])
                                            </div>
                                            <div class="col-md-6 col-sm-12 form-outline {{ $errors->has('ci') ? ' has-danger' : '' }}">
                                                <div class="form-group ">
                                                    <div class="row">
                                                        <div class="col-sm-4" style="padding-right:0" >
                                                            <select class="form-select required" name="nationality" >
                                                                <option value="V">V</option>
                                                                <option value="E">E</option>	
                                                            </select>	
                                                        </div>
                                                        <div class="col-sm-8" style="padding-left:0">
                                                            <input name="ci" type="text" class="form-control {{ $errors->has('ci') ? ' is-invalid' : '' }}" value="{{ old('ci') }}" id="ci"  placeholder="Cédula" title="Es obligatorio una cedula" minlength="7" maxlength="9" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">  
                                                        </div>
                                                    </div>
                                                    <label class="form-label" for="form2Example17">Cedula</label> 
                                                </div>
                                                @include('alerts.feedback', ['field' => 'ci'])
                                            </div>
                                            <div class="col-md-6 col-sm-12 form-outline {{ $errors->has('phone') ? ' has-danger' : '' }}">
                                                <input name="phone" type="text" class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone') }}" id="phone"  placeholder="Teléfono" title="Es obligatorio un telefono" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onpaste="return false" autocomplete="off">
                                                <label class="form-label" for="form2Example17"> Telefono</label>
                                                @include('alerts.feedback', ['field' => 'phone'])
                                            </div>
                                            <div class="col-md-12 col-sm-12 form-outline mb-2 {{ $errors->has('direction') ? ' has-danger' : '' }}">
                                                <input name="direction" type="text" class="form-control {{ $errors->has('direction') ? ' is-invalid' : '' }}" value="{{ old('direction') }}" id="direction"  placeholder="Direccion" title="Es obligatorio un direccion" minlength="5" maxlength="100" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                                <label class="form-label" for="form2Example17"> Direccion</label>
                                                @include('alerts.feedback', ['field' => 'direction'])
                                            </div>
                                            <div class="col-md-6 col-sm-12 form-outline mb-2 {{ $errors->has('email') ? ' has-danger' : '' }}">
                                                <input name="email" type="text" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" id="email"  placeholder="Correo" title="Es obligatorio un correo" minlength="5" maxlength="40"required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                                <label class="form-label" for="form2Example17">Correo Electronico</label>
                                                @include('alerts.feedback', ['field' => 'email'])
                                            </div>
                                            <div class="col-md-6 col-sm-12 form-outline mb-2 {{ $errors->has('password') ? ' has-danger' : '' }}">
                                                <input name="password" type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" value="{{ old('password') }}" id="password" placeholder="Contraseña" title="Es obligatorio una contraseña"  minlength="8" maxlength="20" required >
                                                <label class="form-label" for="form2Example27">Contraseña</label>
                                                @include('alerts.feedback', ['field' => 'password'])
                                            </div>
                                        </div>
                                        <div >
                                            <button class="btn btn-dark btn-lg btn-block" type="submit">Registrar</button>
                                        </div>
                                        <p  style="color: #393f81;">Ya tienes una cuenta? <a href="{{ route('login') }}"
                                            style="color: #393f81;">Inicia sesion aqui</a></p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection