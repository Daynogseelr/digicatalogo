
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'order'])
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
    <div class="modal fade" id="client-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Datos del Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="name" type="text" class="form-control" id="name"  placeholder="Nombre" title="Es obligatorio un nombre" minlength="2" maxlength="20" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                            <label class="form-label" for="form2Example17">Nombre</label>
                            <span id="nameError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="last_name" type="text" class="form-control" id="last_name"  placeholder="Nombre" title="Es obligatorio un apellido" minlength="2" maxlength="20" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                            <label class="form-label" for="form2Example17">Apellido</label>
                            <span id="last_nameError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-4" style="padding-right:0" >
                                        <select disabled class="form-select required" name="nationality" >
                                            <option value="V">V</option>
                                            <option value="E">E</option>	
                                        </select>	
                                    </div>
                                    <div class="col-sm-8" style="padding-left:0">
                                        <input disabled name="ci" type="text" class="form-control" id="ci"  placeholder="Cédula" title="Es obligatorio una cedula" minlength="7" maxlength="10" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">  
                                    </div>
                                </div>
                                <label class="form-label" for="form2Example17">Cedula</label>
                                <span id="ciError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline mb-2">
                            <input disabled name="phone" type="text" class="form-control" id="phone"  placeholder="Teléfono" title="Es obligatorio un telefono" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onpaste="return false" autocomplete="off">
                            <label class="form-label" for="form2Example17"> Telefono</label>
                            <span id="phoneError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="direction" type="text" class="form-control" id="direction"  placeholder="Direccion" title="Es obligatorio un direccion" minlength="5" maxlength="100" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                            <label class="form-label" for="form2Example17"> Direccion</label>
                            <span id="directionError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="email" type="text" class="form-control" id="email"  placeholder="Correo" title="Es obligatorio un correo" minlength="5" maxlength="40"required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
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
                ajax: "{{ url('ajax-crud-datatableOrder') }}",
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
  
  
    function updateOrder(id){
        var quantity = $('#quantityOrder'+id).val();
        if(quantity < 1){
        $('#quantityOrder'+id).val(1);
        quantity = 1;
        }
        $.ajax({
            type:"POST",
            url: "{{ url('updateOrder') }}",
            data: { 'id': id,'quantity': quantity},
            dataType: 'json',
            success: function(res){
                console.log('carrito id: '+res)
            summaryOrder(res)
            }
        });
    }
    function deleteOrder(id){
        $.ajax({
            type:"POST",
            url: "{{ url('deleteOrder') }}",
            data: { 'id': id},
            dataType: 'json',
            success: function(res){
                console.log('carrito id: '+res)
                mostrarOrder(res)
            }
        });
    }
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
                        if (res.status.status == 'PENDIENTE') {
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
                                                        '<div class="col-md-5 product-name">'+
                                                            '<div class="product-name">'+
                                                                '<a href="#">'+element.name+'</a>'+
                                                                '<div class="product-info">'+
                                                                    '<div>Descripcion: <span class="value">'+element.description+'</span></div>'+
                                                                '</div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                        '<div class="col-md-3 quantity">'+
                                                            '<label for="quantity">Cantidad:</label>'+
                                                            '<input id="quantityOrder'+element.id+'" type="number" onChange="updateOrder('+element.id+')" value ="'+element.quantity+'" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()">'+
                                                        '</div>'+
                                                        '<div class="col-md-2 price" style="margin: auto;">'+
                                                            '<span  style="font-size: 15px;" >$'+element.price+'</span>'+
                                                        '</div>'+
                                                        '<div class="col-md-2 delete" style="margin: auto;">'+
                                                            '<a style=" padding: 5px; margin-top: 0.1px !important;" onClick="deleteOrder('+element.id+')" data-toggle="tooltip" class="delete btn btn-danger">'+
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
                        } else  {
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
                        }
                        summaryOrder(id)
                        if (res.status.status == 'PENDIENTE') {
                            var statusA = "'APROBADO'";
                            var statusR = "'RECHAZADO'";
                            $('#btnOrder').html(
                                '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusOrder('+id+','+statusA+')" >Aprobar</button>'+
                                '<a class="btn btn-danger"  onClick="statusOrder('+id+','+statusR+')" >Rechazar</button>'
                            );
                        }  else if (res.status.status == 'APROBADO') {
                            var statusF = "'FINALIZADO'";
                            var statusI = "'INCONCLUSO'";
                            $('#btnOrder').html(
                                '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusOrder('+id+','+statusF+')" >Finalizar</button>'+
                                '<a class="btn btn-danger"  onClick="statusOrder('+id+','+statusI+')" >Inconcluso</button>'
                            );
                        } 
                        console.log(res.status.status)
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
                url: "{{ url('editClient') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    console.log(res)
                    $('#client-modal').modal('show');
                    $('#name').val(res.name);
                    $('#last_name').val(res.last_name);
                    $('#ci').val(res.ci);
                    $('#phone').val(res.phone);
                    $('#direction').val(res.direction);
                    $('#email').val(res.email);
                }
            });
        }  
        function statusOrder(id,status){
            Swal.fire({
                title: "Seguro quieres cambiar el estatus de el pedido?",
                text: "El pedido sera "+status,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si!",
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type:"POST",
                        url: "{{ url('statusOrder') }}",
                        data: { 'id': id, 'status': status },
                        dataType: 'json',
                        success: function(res){
                            console.log(res)
                            $('#order-modal').modal('hide');
                            var oTable = $('#ajax-crud-datatable').dataTable();
                            oTable.fnDraw(false);
                            Swal.fire({
                                title: "Cambio de estatus!",
                                text: "El pedido fue  actualizado.",
                                icon: "success"
                            });
                        }
                    });
                }
            });     
        }  
    </script>
@endsection

