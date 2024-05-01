
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'orderClient'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-4">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>Pedidos</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id="ajax-crud-datatable" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th>Nro</th>
                                            <th>Fecha</th>
                                            <th>Retiro</th>
                                            <th>Total</th>
                                            <th>Estatus</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap cart model -->
<div class="modal fade" id="order-modal" aria-hidden="true" >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-title">Detalles del Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <section class="shopping-cart dark">
              <div class="container">
                <div class="content">
                  <div class="row">
                    <div class="col-md-12 col-lg-9">
                      <div class="items" id="detaillOrder"></div>
                    </div>
                    <div class="col-md-12 col-lg-3">
                      <div class="summary" id="summaryOrder">
                      </div>
                    </div>
                  </div> 
                </div>
              </div>
            </section>
            <div class="col-sm-offset-2 col-sm-12 text-center" id="btnOrder"><br/>
                
            </div>
        </div>
        <div class="modal-footer"></div>
      </div>
    </div>
  </div>
  <!-- end bootstrap model -->
  <!-- boostrap company model -->
  <div class="modal fade" id="company-modal" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title"> Empresa del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="row">
                    <div class="col-md-6 col-sm-12 form-outline">
                        <input name="name" type="text" class="form-control" id="name"  placeholder="Nombre" title="Es obligatorio un nombre" minlength="2" maxlength="20" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off" disabled>
                        <label class="form-label" for="form2Example17">Nombre</label>
                        <span id="nameError" class="text-danger error-messages"></span>
                    </div>
                    <div class="col-md-6 col-sm-12 form-outline">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4" style="padding-right:0" >
                                    <select class="form-select required" name="nationality" disabled>
                                        <option value="J">J</option>	
                                    </select>	
                                </div>
                                <div class="col-sm-8" style="padding-left:0">
                                    <input name="ci" type="text" class="form-control" id="ci"  placeholder="RIF" title="Es obligatorio una cedula" minlength="7" maxlength="10" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off" disabled>  
                                </div>
                            </div>
                            <label class="form-label" for="form2Example17">RIF</label>
                            <span id="ciError" class="text-danger error-messages"></span>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 form-outline mb-2">
                        <input name="phone" type="text" class="form-control" id="phone"  placeholder="Teléfono" title="Es obligatorio un telefono" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onpaste="return false" autocomplete="off" disabled>
                        <label class="form-label" for="form2Example17"> Telefono</label>
                        <span id="phoneError" class="text-danger error-messages"></span>
                    </div>
                    <div class="col-md-6 col-sm-12 form-outline mb-2">
                        <input name="direction" type="text" class="form-control" id="direction"  placeholder="Direccion" title="Es obligatorio un direccion" minlength="5" maxlength="100" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off" disabled>
                        <label class="form-label" for="form2Example17"> Direccion</label>
                        <span id="directionError" class="text-danger error-messages"></span>
                    </div>
                    <div class="col-md-12 col-sm-12 form-outline mb-2">
                        <input name="email" type="text" class="form-control" id="email"  placeholder="Correo" title="Es obligatorio un correo" minlength="5" maxlength="40"required onkeyup="mayus(this);" onpaste="return false" autocomplete="off" disabled>
                        <label class="form-label" for="form2Example17">Correo Electronico</label>
                        <span id="emailError" class="text-danger error-messages"></span>
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
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
            $('#ajax-crud-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableOrderClient') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'order_date', name: 'order_date' },
                    { data: 'retiro', name: 'retiro' },
                    { data: 'total', name: 'total' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false},
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
        });
  
        function mostrarOrder(id){
            $.ajax({
                type:"POST",
                url: "{{ url('mostrarOrder') }}",
                data: { 'id': id},
                dataType: 'json',
                success: function(res){
                    $('#detaillOrder').html('');
                    if (res.success == 'bien') {
                        $('#order-modal').modal('show');
                        res.data.forEach(element => {
                            $('#detaillOrder').append(
                                '<div class="product"  >'+
                                    '<div class="row">'+
                                        '<div class="col-md-3">'+
                                            '<img class="img-fluid mx-auto d-block image" src="/products/'+element.url1+'">'+
                                        '</div>'+
                                        '<div class="col-md-9">'+
                                            '<div class="info">'+
                                                '<div class="row" >'+
                                                    '<div class="col-md-6 product-name">'+
                                                        '<div class="product-name">'+
                                                            '<a href="#">'+element.name+'</a>'+
                                                            '<div class="product-info">'+
                                                                '<div>Descripcion: <span class="value">'+element.description+'</span></div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                    '</div>'+
                                                    '<div class="col-md-3 quantity">'+
                                                        '<label for="quantity">Cantidad:</label>'+
                                                        '<input id="quantityOrder'+element.id+'" type="number" onChange="updateOrder('+element.id+')" value ="'+element.quantity+'" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()" disabled >'+
                                                    '</div>'+
                                                    '<div class="col-md-3  price" style="margin: auto;">'+
                                                        '<span  style="font-size: 15px;" >$'+element.price+'</span>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'
                            );
                        });
                        summaryOrder(id)
                    } else {
                        $('#cart-modal').modal('hide');
                        Swal.fire({
                            title: "Carrito vacio",
                            text: "Su carrito esta vacio, elija algun producto",
                            icon: "question"
                        });
                    } 
                }
            });
        }   
        function summaryOrder(id){
            $.ajax({
                type:"POST",
                url: "{{ url('summaryOrder') }}",
                data: { 'id': id},
                dataType: 'json',
                success: function(res){
                    console.log(res)
                    $('#summaryOrder').html('');
                    $('#summaryOrder').html(
                        '<h3>Total</h3>'+
                        '<div class="summary-item"><span class="text">Subtotal</span><span class="price">$'+res+'</span></div>'+
                        '<div class="summary-item"><span class="text">Descuento</span><span class="price">$0</span></div>'+
                        '<div class="summary-item"><span class="text">Total</span><span class="price">$'+res+'</span></div>'
                    );
                }
            });
        } 
        function editFunc(id){
            $.ajax({
                type:"POST",
                url: "{{ url('edit') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#company-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#ci').val(res.ci);
                    $('#phone').val(res.phone);
                    $('#direction').val(res.direction);
                    $('#email').val(res.email);
                }
            });
        }  
    </script>
@endsection
