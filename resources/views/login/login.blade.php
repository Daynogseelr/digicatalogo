@extends('app', ['class' => 'login-page', 'page' => __(''), 'contentClass' => 'login-page', 'pageSlug' => 'login'])
@section ('content')
    <section class="vh-100" style="background-color: #ffffffd2;">
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
                                    <form method="get" action="{{ route('logeo') }}" autocomplete="off">
                                        @csrf
                                        @method('get')
                                        @include('alerts.success')
                                        <div class="d-flex align-items-center pb-1">
                                            <img src="../assets/img/DIGI.png"  style=" padding:0px; width: 50% !important; height: 190px !important; margin:auto !important;  margin-bottom: -50px !important; margin-top: -50px !important;" >
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Inicio de sesion</h5>
                                        @include('alerts.feedback', ['field' => 'email2'])
                                        <div class="form-outline mb-4 {{ $errors->has('email') ? ' has-danger' : '' }}">
                                            <input name="email" type="text" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}"id="email"  placeholder="Correo" title="Es obligatorio un correo" minlength="5" maxlength="40"required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                            <label class="form-label" for="form2Example17">Correo Electronico</label>
                                            @include('alerts.feedback', ['field' => 'email'])
                                        </div>
                                        <div class="form-outline mb-4 {{ $errors->has('password') ? ' has-danger' : '' }}">
                                            <input name="password" type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" value="{{ old('password') }}" id="password" placeholder="Contraseña" title="Es obligatorio una contraseña"  minlength="8" maxlength="20" required >
                                            <label class="form-label" for="form2Example27">Contraseña</label>
                                            @include('alerts.feedback', ['field' => 'password'])
                                        </div>
                                        <div class="pt-1 mb-4">
                                        <button class="btn btn-dark btn-lg btn-block" type="submit">Iniciar</button>
                                        </div>
                                        <a class="small text-muted" href="">Has olvidado tu contraseña?</a>
                                        <p class="mb-5 pb-lg-2" style="color: #393f81;">No tienes una cuenta? <a href="{{ route('registerIndex') }}"
                                            style="color: #393f81;">Registrate aqui</a></p>
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