@foreach($products as $pro)
    <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card" style="margin-bottom: 10px;  height: 360px;">
            <a class="a" onClick="mostrarProduct({{ $pro->id }})" href="javascript:void(0)">
                <img src="/products/{{ $pro->url1 }} "
                    class="card-img-top mx-auto"
                    style="height: 150px; width: 150px;display: block;  margin-top: 10px;"
                    alt="{{ $pro->name }}"
                >
                <div class="card-body"  >
                    <h6 class="card-title" style="font-size: 13px;" >{{ $pro->name }}</h6>
            </a>
                    <p>${{ $pro->price }}</p>
                    @if ($pro->status == 0)
                        <p class="card-title" style=" color:red !important;"> Agotado</p>
                    @elseif($pro->status == 1)
                        <p class="card-title" style=" color:green !important;"> Disponible</p>
                    @else
                        <p class="card-title" style=" color:orange !important;"> Por encargo</p>
                    @endif
                    <form action="javascript:void(0)" id="form-add{{ $pro->id }}" name="productForm2" class="form-horizontal " method="POST" enctype="multipart/form-data">
                        <input type="hidden" value="{{ $pro->id }}" name="id_product">
                        <input type="hidden" value="{{ $pro->price }}" name="price">
                        <input type="hidden" value="1" name="quantity">
                        <input type="hidden" value="{{ $pro->id_company }}"  name="id_company">
                        <div class="card-footer" style=" height: 100% !important;">
                            <div class="row">
                                <button type="button" onclick="addpro({{ $pro->id }})" class="btn btn-primary btn-sm" class="tooltip-test" title="Agregar a carrito">
                                    <i class="fa fa-shopping-cart"></i> Agregar a carrito
                                </button>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
@endforeach
{!! $products->render(); !!}

