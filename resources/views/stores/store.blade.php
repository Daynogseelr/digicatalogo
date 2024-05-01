@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'store'])
@section('content')
    <div class="container" style="margin-top: 30px">
        <div>

        </div>
        <div class="row justify-content-center productBuscador">
            @component('stores.products')
                @slot('products',$products)
            @endcomponent
        </div>
    </div>
     <!-- boostrap product model -->
 <div class="modal fade" id="product-modal" aria-hidden="true" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Detalle del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-5" id="divcarousel" >       
                    </div>
                    <div class="col-lg-7" >
                        <h6 id="divname"></h6>
                        <P id="divprice"></P>
                        <br>
                        <p id="divdescription"></p>
                        <br>
                        <form action="javascript:void(0)" id="productForm" name="productForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                            <input type="hidden" value="" id="id_product" name="id_product">
                            <input type="hidden" value="" id="price" name="price">
                            <input type="hidden" value="{{$id_company}}"  name="id_company">
                            <div class="row">
                                <div class="col-md-3 col-sm-5 form-outline">
                                    <input name="quantity" type="number" class="form-control" id="quantity" value="1"  placeholder="1" title="Es obligatorio una cantidad" min="1" required onkeypress='return validaMonto(event)'  onpaste="return false" autocomplete="off"  onkeydown="filtro()">
                                    <label class="form-label" for="form2Example17">Cantidad</label>
                                    <span id="quantityError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-8 col-sm-6 form-outline">
                                    <button class="btn btn-primary" class="tooltip-test" title="agregar a carrito">
                                        <i class="fa fa-shopping-cart"></i> Agregar a carrito
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<!-- end bootstrap model -->
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).on('click','.pagination a', function (e) {
            e.preventDefault();
            $.ajax({
                type:"GET",
                url: "{{ url('indexStore') }}",
                data: {page: $(this).attr('href').split('page=')[1],scope:$("#buscador").val() },
                dataType: 'json',
                success: function(res){
                    $('.productBuscador').html(res)
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    } 
                }    
            })
        });
       
        $(document).ready( function () {

            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#category' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",  
                language: "es"
            }); 
            var id_company = $('#id_company').val();
            quantityCart(id_company)
        });
        function mostrarProduct(id){
            console.log(id)
            $.ajax({
                type:"POST",
                url: "{{ url('mostrarProduct') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#product-modal').modal('show');
                    $('#divname').html(res.name);
                    $('#divprice').html('<p>Precio: $'+res.price+'</p>');
                    $('#divdescription').html(res.description);
                    $('#divcarousel').html(
                        '<div id="carouselExampleIndicators'+res.id+'" class="carousel slide" data-bs-ride="carousel"  style="border-radius: 1rem 0 0 1rem; height:100% !important;">'+
                            '<div class="carousel-indicators" id="buttonCarousel">'+ 
                            '</div>'+
                            '<div class="carousel-inner" id="itemCarousel" >'+
                            '</div>'+
                            '<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators'+res.id+'" data-bs-slide="prev">'+
                                '<span class="carousel-control-prev-icon" aria-hidden="true"></span>'+
                                '<span class="visually-hidden">Previous</span>'+
                            '</button>'+
                            '<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators'+res.id+'" data-bs-slide="next">'+
                                '<span class="carousel-control-next-icon" aria-hidden="true"></span>'+
                                '<span class="visually-hidden">Next</span>'+
                            '</button>'+
                        '</div>'
                    );
                    if (res.url1 != null) {
                        $('#buttonCarousel').append(
                        '<button type="button" data-bs-target="#carouselExampleIndicators'+res.id+'" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item  active" data-bs-interval="3000">'+
                                '<img src="/products/'+res.url1+'" class="d-block w-100" alt="..."  style="border-radius: 1rem 0 0 1rem; height:100% !important;">'+
                            '</div>'
                        );
                    }
                    if (res.url2 != null) {
                        $('#buttonCarousel').append(
                        '<button type="button" data-bs-target="#carouselExampleIndicators'+res.id+'" data-bs-slide-to="1" aria-label="Slide 2"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item" data-bs-interval="3000">'+
                                '<img src="/products/'+res.url2+'" class="d-block w-100" alt="..."  style="border-radius: 1rem 0 0 1rem; height:100% !important;">'+
                            '</div>'
                        );
                    }
                    if (res.url3 != null) {
                        $('#buttonCarousel').append(
                        '<button type="button" data-bs-target="#carouselExampleIndicators'+res.id+'" data-bs-slide-to="2" aria-label="Slide 3"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item">'+
                                '<img src="/products/'+res.url3+'" class="d-block w-100" alt="..."  style="border-radius: 1rem 0 0 1rem; height:100% !important;">'+
                            '</div>'
                        );
                    }
                    $('#quantity').val(1);
                    $('#id_product').val(res.id);
                    $('#price').val(res.price);
                    $('#carouselExampleIndicators'+res.id+'').carousel({
                        interval: 1000
                    })
                }
               
            });
            
        }   
        $('#productForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            var id_company = $('#id_company').val();
            $.ajax({
                type:'POST',
                url: "{{ url('addCart')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#product-modal").modal('hide');
                    if (data.success == 'success') {
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Producto agregado al carrito",
                            showConfirmButton: false,
                            timer: 1500
                        }); 
                    } else {
                        Swal.fire({
                            title: "Producto ya Agregado",
                            text: "Este producto ya esta agregado a tu carrito",
                            icon: "question"
                        });
                    }
                    
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    } 
                }    
            });
            quantityCart(id_company)
        });
        function addpro(e){
            var formulario = $('#form-add'+e+'')[0];
            var formData = new FormData(formulario);
            var id_company = $('#id_company').val();
            console.log('entro');
            $.ajax({
                type:'POST',
                url: "{{ url('addCart')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    if (data.success == 'success') {
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Producto agregado al carrito",
                            showConfirmButton: false,
                            timer: 1500
                        }); 
                    } else {
                        Swal.fire({
                            title: "Producto ya Agregado",
                            text: "Este producto ya esta agregado a tu carrito",
                            icon: "question"
                        });
                    }
                   
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    } 
                }    
            });
            quantityCart(id_company)
        }
        function filtro(){
            var tecla = event.key;
            if (['.',',','e','-','+'].includes(tecla)) {
            event.preventDefault()      
            }
        }
    </script>
@endsection



