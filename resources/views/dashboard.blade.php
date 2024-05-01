@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'dashboard'])
@section('content')
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <a class="a " href="{{ route('indexOrder') }}">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold "  style="color: black !important;">Pedidos</p>
                      <h5 class="font-weight-bolder">
                        {{$countOrder}}
                      </h5>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                      <i class="fa-solid fa-cart-flatbed-suitcase text-sm opacity-10" aria-hidden="true" ></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <a href="{{ route('indexAddCategory') }}" class="a">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold" style="color: black !important;">Categorias</p>
                      <h5 class="font-weight-bolder">
                        {{$countCategory}}
                      </h5>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style=" background-color:purple !important;">
                      <i class="fa-solid fa-table-list text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <a class="a" href="{{ route('indexProduct') }}">
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold" style="color: black !important;">Productos</p>
                      <h5 class="font-weight-bolder">
                        {{$countProduct}}
                      </h5>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                      <i class="ni ni-app text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <a href="{{ route('indexClient') }}" class="a"> 
            <div class="card">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-8">
                    <div class="numbers">
                      <p class="text-sm mb-0 text-uppercase font-weight-bold" style="color: black !important;">Clientes</p>
                      <h5 class="font-weight-bolder">
                        {{$countClient}}
                      </h5>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                      <i class="fa-solid fa-user-astronaut text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Precio de productos</h6>
            </div>
            <div class="card-body p-3">
              <div class="card-body" >
                  <div class="tabla table-responsive" style="font-size: 13px;"> 
                      <table class="table table-striped" id="ajax-crud-datatable" style="font-size: 13px; width: 98% !important;">
                          <thead>
                              <tr>
                                  <th>Cod.</th>
                                  <th>Nombre</th>
                                  <th>Descripcion</th>
                                  <th>Precio $</th>
                                  <th>Precio2 $</th>
                              </tr>
                          </thead>
                      </table>
                  </div>
              </div>
          </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-6 mb-lg-0 mb-4">
          <div class="card ">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between">
                <h6 class="mb-2">Productos mas vendidos</h6>
              </div>
            </div>
            <div class="card-body p-3 ">
              
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">Clientes mas destacados</h6>
            </div>
            <div class="card-body p-3">
              
            </div>
          </div>
        </div>
      </div>
      <footer class="footer pt-3  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                made with <i class="fa fa-heart"></i> by
                <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative Tim</a>
                for a better web.
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="https://www.creative-tim.com" class="nav-link text-muted" target="_blank">Creative Tim</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted" target="_blank">About Us</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/blog" class="nav-link text-muted" target="_blank">Blog</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted" target="_blank">License</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
$(document).ready( function () {
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  }); 
  $('#ajax-crud-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ url('ajax-crud-datatableProductDashboard') }}",
      columns: [
          { data: 'id', name: 'id' },
          { data: 'name', name: 'name' },
          { data: 'description', name: 'description' },
          { data: 'price', name: 'price' },
          { data: 'price', name: 'price' }
      ],
      order: [[0, 'desc']],
      "oLanguage": {
          "sProcessing":     "Procesando...",
          "sLengthMenu": 'Mostrar <select>'+
              '<option value="10">10</option>'+
              '<option value="20">20</option>'+
              '<option value="30">30</option>'+
              '<option value="40">40</option>'+
              '<option value="50">50</option>'+
              '<option value="-1">Todos</option>'+
              '</select> registros',    
          "sZeroRecords":    "No se encontraron resultados",
          "sEmptyTable":     "Ningún dato disponible en esta tabla",
          "sInfo":           "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
          "sInfoEmpty":      "Mostrando del 0 al 0 de un total de 0 registros",
          "sInfoFiltered":   "(de _MAX_ existentes)",
          "sInfoPostFix":    "",
          "sSearch":         "Buscar:",
          "sUrl":            "",
          "sInfoThousands":  ",",
          "sLoadingRecords": "Por favor espere - cargando...",
          "oPaginate": {
              "sFirst":    "Primero",
              "sLast":     "Último",
              "sNext":     "Siguiente",
              "sPrevious": "Anterior"
          },
          "oAria": {
              "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
              "sSortDescending": ": Activar para ordenar la columna de manera descendente"
          }
      }
  });
  $('#single-select-field' ).select2( {
      theme: "bootstrap-5",
      width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
      placeholder: $( this ).data( 'placeholder' ),
      dropdownCssClass: "color",
      selectionCssClass: "form-select",  
      dropdownParent: $('#product-modal .modal-body'),
      language: "es"
  }); 
});
  </script>
@endsection
