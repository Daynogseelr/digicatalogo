<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pagina</a></li>
        <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{$pageSlug}}</li>
        <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{auth()->user()->name}}</li>
      </ol>
    </nav>
    <div class="collapse navbar-collapse " id="navbar">
      <div class="ms-md-auto pe-md-5 d-flex align-items-center" style="width: 70% !important;">
        <div class="input-group"  >
          @if ($pageSlug == 'store')
            <select onChange="buscarProduct({{$id_company}})" class="form-select" name="id_category" id="category" data-placeholder="Categorias">
              <option></option>  
              <option value="TODAS">TODAS</option>
              @foreach ($categories as $category)
                  <option value="{{$category->id}}">{{$category->name}}</option>
              @endforeach
            </select>
            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
            <input id="buscador" style="width: 60% !important;" type="text" class="form-control" placeholder=" Buscador..." onKeyup="buscarProduct({{$id_company}})">   
          @endif
        </div>
      </div>
      <ul class="navbar-nav  justify-content-end "> 
        <li class="nav-item">
          <div class="iconn">
            @if ($pageSlug == 'store') 
                <a class="a" onClick="mostrarCart({{$id_company}})" href="javascript:void(0)">
                  <i style="font-size: 20px !important;" class="fa fa-shopping-cart i"></i> 
                  <span  id="quantityCart" class="spann">{{$quantity}}</span>
                </a>
            @endif
          </div>
          <span class="badge badge-pill badge-dark">
        </li>
        <li class="nav-item d-flex align-items-center " style="display: inline-block !important; text-align: right !important;">
          <a href="{{ route('logout') }}" class="nav-link text-white font-weight-bold px-0">
            <i class="fa fa-user me-sm-1"></i>
            <span class="d-sm-inline d-none">Salir</span>
          </a>
        </li>
        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line bg-white"></i>
              <i class="sidenav-toggler-line bg-white"></i>
              <i class="sidenav-toggler-line bg-white"></i>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
@if ($pageSlug == 'store')
<!-- boostrap cart model -->
<div class="modal fade" id="cart-modal" aria-hidden="true" >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-title">Carrito de Compras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="javascript:void(0)" id="cartForm" name="cartForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id_cart" id="id_cart">
          <input type="hidden" name="id_company" id="id_company" value="{{$id_company}}">
          <section class="shopping-cart dark">
            <div class="container">
              <div class="content">
                <div class="row">
                  <div class="col-md-12 col-lg-9">
                    <div class="items" id="detaillCart"></div>
                  </div>
                  <div class="col-md-12 col-lg-3">
                    <div class="summary" id="summary">
                    </div>
                  </div>
                  <div class="col-md-12 col-lg-12">
                    <label for="exampleFormControlInput1" class="form-label">Retiro por</label>
                    <select name="retiro" class="form-select" aria-label="Default select example" required>
                      <option value="" selected>Seleccione una opcion</option>
                      <option value="TIENDA">Tienda</option>
                      <option value="DELIVERY">Delivery</option>
                      <option value="ENVIO">Envio</option>
                    </select>
                    <span id="retiroError" class="text-danger error-messages"></span>
                  </div>
                </div> 
                <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                  <button title="carro" onclick="enviarCart()" type="button" class="btn btn-primary">Enviar Pedido</button>
                </div>
              </div>
            </div>
          </section>
        </form>
      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>
<!-- end bootstrap model -->
@endif
  <script type="text/javascript"> 
    function mostrarCart(id_company){
      $.ajax({
        type:"POST",
        url: "{{ url('mostrarCart') }}",
        data: { 'id_company': id_company},
        dataType: 'json',
        success: function(res){
          $('#detaillCart').html('');
          if (res.success == 'bien') {
            res.data.forEach(element => {
              $('#cart-modal').modal('show');
              $('#detaillCart').append(
                '<div class="product"  >'+
                    '<div class="row">'+
                        '<div class="col-md-3">'+
                            '<img class="img-fluid mx-auto d-block image" src="/products/'+element.url1+'">'+
                        '</div>'+
                        '<div class="col-md-9">'+
                            '<div class="info">'+
                                '<div class="row" >'+
                                    '<div class="col-md-5 product-name">'+
                                        '<div class="product-name">'+
                                            '<a href="#">'+element.name+'</a>'+
                                            '<div class="product-info">'+
                                                '<div>Descripcion: <span class="value">'+element.description+'</span></div>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="col-md-3 quantity">'+
                                        '<label for="quantity">Cantidadll:</label>'+
                                        '<input id="quantity'+element.id+'" type="number" onChange="updateCart('+element.id+')" value ="'+element.quantity+'" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()">'+
                                    '</div>'+
                                    '<div class="col-md-2 price" style="margin: auto;">'+
                                        '<span>$'+element.price+'</span>'+
                                    '</div>'+
                                    '<div class="col-md-2 delete" style="margin: auto;">'+
                                      '<a style=" padding: 5px; margin-top: 0.1px !important;" onClick="deleteCart('+element.id+')" data-toggle="tooltip" class="delete btn btn-danger">'+
                                        '<i class="fa-solid fa-trash-can"></i>'+
                                        '</a>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'
              );
            });
            summary(id_company)
          } else {
            $('#cart-modal').modal('hide');
            Swal.fire({
              title: "Carrito vacio",
              text: "Su carrito esta vacio, elija algun producto",
              icon: "question"
            });
            quantityCart(id_company)
          } 
        }
    });
  }    
  function summary(id_company){
    $.ajax({
      type:"POST",
      url: "{{ url('summaryCart') }}",
      data: { 'id_company': id_company},
      dataType: 'json',
      success: function(res){
        console.log(res)
        $('#summary').html('');
        $('#summary').html(
          '<h3>Total</h3>'+
          '<div class="summary-item"><span class="text">Subtotal</span><span class="price">$'+res+'</span></div>'+
          '<div class="summary-item"><span class="text">Descuento</span><span class="price">$0</span></div>'+
          '<div class="summary-item"><span class="text">Total</span><span class="price">$'+res+'</span></div>'
        );
        quantityCart(id_company)
      }
    });
  }
  function updateCart(id){
    var id_company = $('#id_company').val();
    var quantity = $('#quantity'+id).val();
    if(quantity < 1){
      $('#quantity'+id).val(1);
      quantity = 1;
    }
      $.ajax({
        type:"POST",
        url: "{{ url('updateCart') }}",
        data: { 'id': id, 'id_company': id_company,'quantity': quantity},
        dataType: 'json',
        success: function(res){
          console.log(res)
          summary(id)
        }
    });
  }
  function deleteCart(id){
    var id_company = $('#id_company').val();
    $.ajax({
      type:"POST",
      url: "{{ url('deleteCart') }}",
      data: { 'id': id, 'id_company': id_company},
      dataType: 'json',
      success: function(res){
        mostrarCart(res)
      }
    });
  }
  function quantityCart(id_company){
    $.ajax({
      type:"POST",
      url: "{{ url('quantityCart') }}",
      data: { 'id_company': id_company},
      dataType: 'json',
      success: function(res){
        $('#quantityCart').html(res); 
      }
    });
  }
  function buscarProduct(id_company){
    var category = $('#category').val(); 
    console.log(category)
    console.log(id_company)
    $.ajax({
      type:"GET",
      url: "{{ url('indexStore') }}",
      data: {id_company: id_company, category: category, scope: $("#buscador").val() },
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
  }  

  function enviarCart(){
    $('.error-messages').html('');
    var formulario = $('#cartForm')[0];
    var formData = new FormData(formulario);
    $.ajax({
      type:"POST",
      url: "{{ route('storeCart') }}",
      processData: false,
      contentType: false,
      cache: false,
      dataType: 'json', 
      data: formData,
      success: function(res){
        $('#cart-modal').modal('hide');
        Swal.fire({
          position: "top-end",
          icon: "success",
          title: "Pedido enviado con exito",
          showConfirmButton: false,
          timer: 1500
        });
        quantityCart()
      },
      error: function(error) {
        if (error) {
            console.log(error.responseJSON.errors);
            console.log(error);
            $('#retiroError').html(error.responseJSON.errors.retiro);
        } 
      }    
    });
  }
  
</script> 
<!-- End Navbar -->
 