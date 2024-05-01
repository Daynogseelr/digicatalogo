@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'storeIndex'])
@section('content')
    <div class="container" style="margin-top: 10px">
        <div class="card mb-3">
            <div class="card-header" style="text-align: center; font-size: 16px; "><h4 class="card-title">Tiendas de Empresas</h4></div>
            <div class="card-body">
                <div class="row justify-content-center">
                    @foreach($companies as $company)
                        <div class="col-md-4">
                            <div class="card">
                                <img src="/logos/{{$company->logo}}" width="100%" height="300px" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">{{$company->name}}</h5>
                                    <p class="card-text">Los mejores productos, los mejores precios.</p>
                                </div>
                                <form class="text-end"  method="get" action="{{ route('indexStore') }}" autocomplete="off">
                                    @csrf
                                    @method('get')
                                    <input type="hidden" value="{{ $company->id }}" name="id_company">
                                    <button type="submit" class="card-link text-end" style="margin: 10px; ">Ir a la tienda >> </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
          </div>
    </div>
@endsection