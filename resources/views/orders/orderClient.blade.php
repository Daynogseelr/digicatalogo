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
                                        <h4>{{__('My Orders')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatable"
                                    style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Num')}}</th>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Withdrawal')}}</th>
                                            <th class="text-center">{{__('Total')}}</th>
                                            <th class="text-center">{{__('Seller')}}</th>
                                            <th class="text-center">{{__('Status')}}</th>
                                            <th class="text-center">{{__('Action')}}</th>
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
    <div class="modal fade" id="order-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Order details')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <section class="shopping-cart dark">
                        <div class="container">
                            <div class="content">
                                <div class="row">
                                    <div class="col-md-12 col-lg-8">
                                        <div class="items" id="detaillOrder"></div>
                                    </div>
                                    <div class="col-md-12 col-lg-4">
                                        <div class="summary">
                                            <h3>{{__('Total')}}</h3>
                                            <div class="summary-item" id="summaryOrder">
                                            </div>
                                            <div class="summary-item" id="summaryDescuento">
                                            </div>
                                            <div class="summary-item" id="summaryAdditional">
                                            </div>
                                            <div class="summary-item" id="summaryTotal">
                                            </div>
                                            <div class="summary-item" id="summaryTotalBs">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <div class="col-sm-offset-2 col-sm-12 text-center" id="btnOrder"><br />

                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap company model -->
    <div class="modal fade" id="company-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Order Company')}} </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input name="name" type="text" class="form-control" id="name" disabled>
                            <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                        </div>
                        <div class="col-md-6 col-sm-6 form-outline">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-4 col-sm-4" style="padding-right:0">
                                        <select class="form-select required" name="nationality" disabled>
                                            <option value="J">J</option>
                                        </select>
                                    </div>
                                    <div class="col-8 col-sm-8" style="padding-left:0">
                                        <input name="ci" type="text" class="form-control" id="ci" disabled>
                                    </div>
                                </div>
                                <label class="form-label" for="form2Example17">{{__('Identification card')}}</label>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 form-outline mb-2">
                            <input name="phone" type="text" class="form-control" id="phone" disabled>
                            <label class="form-label" for="form2Example17"> {{__('Phone')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input name="direction" type="text" class="form-control" id="direction" disabled>
                            <label class="form-label" for="form2Example17"> {{__('Direction')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input name="email" type="text" class="form-control" id="email" disabled>
                            <label class="form-label" for="form2Example17">{{__('Email')}}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap payment model -->
    <div class="modal fade" id="payment-modal" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Payment record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="paymentForm" name="paymentForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_playment_cart" id="id_playment_cart">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="type">{{__('Payment type')}}:</label>
                                    <select class="form-select" name="type" id="type">
                                        <option value="">{{__('Select a payment type')}}</option>
                                        <option value="TRANSFERENCIA">{{__('Wire transfer')}}</option>
                                        <option value="PAGO MOVIL">{{__('Mobile Payment')}}</option>
                                        <option value="OTRO">{{__('Other')}}</option>
                                    </select>
                                </div>
                                <span id="typeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label for="bank">{{__('Bank to which the operation was carried out')}}:</label>
                                    <select class="form-select" name="bank" id="bank">
                                        <option value="">{{__('Select a bank')}}</option>
                                        <option value="BANCO NACIONAL DE CREDITO">Banco Nacional de Credito</option>
                                        <option value="BANCO DE VENEZUELA">Banco de Venezuela</option>
                                        <option value="BANCARIBE">Bancaribe</option>
                                        <option value="BANCO PROVINCIAL">Banco Provincial</option>
                                        <option value="BANO MUNDIAL">Banco Mundial</option>
                                        <option value="BANCO MERCANTIL">Banco Mercantil</option>
                                        <option value="BANCO EXTERIOR">Banco Exterior</option>
                                        <option value="OTRO">{{__('Other')}}</option>
                                    </select>
                                </div>
                                <span id="bankError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Reference')}}</label>
                                    <input name="reference" type="text" class="form-control" id="reference" placeholder="Referencia"
                                    title="Es obligatorio un nombre" minlength="1" maxlength="30" required>
                                    <span id="referenceError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Amount')}}</label>
                                    <input name="amount" type="text" class="form-control" id="amount"  placeholder="Precio" title="Es obligatorio un precio" minlength="1" maxlength="10" required onkeypress='return validaMonto(event)' onkeyup="mayus(this);"  autocomplete="off"> 
                                    <span id="amountError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Payment Capture')}}</label>
                                    <input name="capture" type="file" class="form-control" id="capture"  accept="image/*" title="Es obligatorio una Imagen" >
                                    <span id="captureError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                                <button type="submit" class="btn btn-primary" id="btn-save">{{__('Process')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap verpayment model -->
    <div class="modal fade" id="verpayment-modal" aria-hidden="true">
        <div class="modal-dialog"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Payment record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="vertype">{{__('Payment type')}}:</label>
                                <input name="vertype" type="text" class="form-control" id="vertype" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label for="verbank">{{__('Bank to which the operation was carried out')}}:</label>
                                <input name="verbank" type="text" class="form-control" id="verbank" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="verreference">{{__('Reference')}}</label>
                                <input name="verreference" type="text" class="form-control" id="verreference" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="veramount">{{__('Amount')}}</label>
                                <input name="veramount" type="text" class="form-control" id="veramount" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="vercapture">{{__('Payment Capture')}}</label>
                                <div  id="vercapture" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    @include('footer')
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#ajax-crud-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableOrderClient') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'order_date',
                        name: 'order_date'
                    },
                    {
                        data: 'retiro',
                        name: 'retiro'
                    },
                    { data: 'total', render: function(data, type, row){
                        var total = parseFloat(row.total) + parseFloat(row.additional);
                        return total.toFixed(2);
                    }},
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            if (row.type == 'EMPRESA') {
                                return `${row.name}`;
                            } else {
                                return `${row.name} ${row.last_name}`;
                            }
                        }
                    },
                    { data: 'status', render: function(data, type, row){
                        if (data == 'PENDIENTE') {
                            return '<td class="text-info"><span class="text-info">{{__('PENDING')}}</span></td>';
                        } else if (data == 'APROBADO') {
                            return '<td class="text-success"><span class="text-success">{{__('APPROVED')}}</span></td>';
                        } else if (data == 'FINALIZADO') {
                            return '<td class="text-primary"><span class="text-primary">{{__('FINALIZED')}}</span></td>';
                        } else if (data == 'RECHAZADO') {
                            return '<td class="text-danger"><span class="text-danger">{{__('REFUSED')}}</span></td>';
                        } else if (data == 'INCONCLUSO') {
                            return '<td class="text-warning"><span class="text-warning">{{__('UNFINISHED')}}</span></td>';
                        }
                    }},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                order: [
                    [1, 'desc']
                ],
                 lengthMenu: [ // Define las opciones del menú de "Mostrar"
                    [20, 30, 40, 50, -1], // Valores reales
                    ['20', '30', '40', '50', 'Todos'] // Texto a mostrar
                ],
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="20" selected>20</option>' +
                        '<option value="30">30</option>' +
                        '<option value="40">40</option>' +
                        '<option value="50">50</option>' +
                        "<option value='-1'>{{__('All')}}</option>" +
                        "</select> {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} (_START_ {{__('to the')}} _END_) {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
                    "sInfoEmpty": "{{__('Showing 0 to 0 of a total of 0 records')}}",
                    "sInfoFiltered": "({{__('of')}} _MAX_ {{__('existents')}})",
                    "sInfoPostFix": "",
                    "sSearch": "{{__('Search')}}:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "{{__('Please wait - loading')}}...",
                    "oPaginate": {
                        "sFirst": "{{__('First')}}",
                        "sLast": "{{__('Last')}}",
                        "sNext": "{{__('Next')}}",
                        "sPrevious": "{{__('Previous')}}"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
            function centerTableContent() {
                $('#ajax-crud-datatable tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(3)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(5)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(6)').addClass('text-center');
            }
        });

        function mostrarOrder(id) {
            $('#btnOrder').html('');
            $.ajax({
                type: "POST",
                url: "{{ url('mostrarOrder') }}",
                data: {
                    'id': id
                },
                dataType: 'json',
                success: function(res) {
                    $('#detaillOrder').html('');
                    if (res.success == 'bien') {
                        var urls = "'/products/product.png'";
                        $('#order-modal').modal('show');
                        res.data.forEach(element => {
                            $('#detaillOrder').append(
                                '<div class="product"  >' +
                                '<div class="row">' +
                                '<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-4 col-sm-5 col-6">' +
                                '<img class="img-fluid mx-auto d-block image" src="/products/' +
                                element.url1 + '" onerror="this.src=' + urls +'">' +
                                '</div>' +
                                '<div class="col-xxl-9 col-xl-9 col-lg-9 col-md-8 col-sm-7 col-6">' +
                                '<div class="info">' +
                                '<div class="row" >' +
                                '<div class="col-md-6 product-name">' +
                                '<div class="product-name">' + element.name + '</div>' +
                                '</div>' +
                                '<div class="col-md-3 quantity">' +
                                '<label for="quantity">{{__('Quantity')}}:</label>' +
                                '<input id="quantityOrder' + element.id +
                                '" type="number" onChange="updateOrder(' + element.id +
                                ')" value ="' + element.quantity +
                                '" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()" disabled >' +
                                '</div>' +
                                '<div class="col-md-3  price" style="margin: auto;">' +
                                '<span  style="font-size: 15px;" >$' + element.price + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>'
                            );
                        });
                        if (res.status.status == 'APROBADO') {
                            if (res.playment) {
                                $('#btnOrder').html(
                                    '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="verpagoOrder(' +id + ')" >{{__('See Payment')}}</a>' 
                                );
                            } else {
                                $('#btnOrder').html(
                                    '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="pagoOrder(' +id + ')" >{{__('Process')}}</a>' 
                                );
                            }
                        }else{
                            if (res.playment) {
                                $('#btnOrder').html(
                                    '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="verpagoOrder(' +id + ')" >{{__('See Payment')}}</a>' 
                                );
                            }
                        }

                        summaryOrder(id)
                    } else {
                        $('#cart-modal').modal('hide');
                        Swal.fire({
                            title: "{{__('empty Cart')}}",
                            text: "{{__('there is nothing to show')}}",
                            icon: "question"
                        });
                    }
                }
            });
        }

        function summaryOrder(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('summaryOrder') }}",
                data: {
                    'id': id
                },
                dataType: 'json',
                success: function(res) {
                    $('#summaryOrder').html(
                        '<span class="text">{{__('Subtotal')}}</span><span class="price">$' +
                        res.subtotal + '</span>'
                    );
                    $('#summaryDescuento').html(
                        '<span class="text">{{__('Discount')}}</span><span class="price">$' +
                        res.discount + '</span>'
                    );
                    if (res.additional == null) {
                        res.additional = 0;
                    }
                    $('#summaryAdditional').html(
                        '<span class="text">{{__('Transfer')}}</span><span class="price">$' +
                        res.additional + '</span>'
                    );
                    $('#summaryTotal').html(
                        '<span class="text">{{__('Total')}}</span><span class="price">$' +
                        res.totalAdditional + '</span>'
                    );
                    $('#summaryTotalBs').html(
                        '<span class="text">{{__('Total')}} Bs</span><span class="price">' +
                        res.totalBs + '</span>'
                    );
                }
            });
        }

        function editFunc(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('edit') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
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
        function pagoOrder(id) {
            $('#payment-modal').modal('show');
            $('#id_playment_cart').val(id);
        }
        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('playmentClient')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#payment-modal").modal('hide');
                    $('#order-modal').modal('hide');
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    $("#btn-save").html('Enviar');
                    $("#btn-save"). attr("disabled", false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Registro guardado exitosamente",
                        showConfirmButton: false,
                        timer: 1500
                    }); 
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#typeError').html(error.responseJSON.errors.type);
                        $('#bankError').html(error.responseJSON.errors.bank);
                        $('#referenceError').html(error.responseJSON.errors.reference);
                        $('#amountError').html(error.responseJSON.errors.amount);
                        $('#captureError').html(error.responseJSON.errors.capture);
                    } 
                }    
            });
        });
        function verpagoOrder(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('verPlaymentClient') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#verpayment-modal').modal('show');
                    $('#vertype').val(res.type);
                    $('#verbank').val(res.bank);
                    $('#verreference').val(res.reference);
                    $('#veramount').val(res.amount);
                    $('#vercapture').html(
                        '<img  src="/payments/'+res.capture+'"'+
                        'class="card-product-img-top mx-auto"'+
                        'style="height: 150px; width: 150px;display: block;  margin-top: 10px;"'+
                        'alt="capture">'
                    );
                }
            });
        }
    </script>
@endsection
