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
                                    <div class="col-9 col-sm-9">
                                        <h4>{{__('Orders')}}</h4>
                                    </div>
                                    <div class="col-3 col-sm-3 text-end">
                                        @if (auth()->user()->type == "EMPRESA")
                                            <a class="btn btn-primary" onClick="addAllSeller()" href="javascript:void(0)"> 
                                                {{__('All')}} <br> <i class="fa-solid fa-user-plus"></i>
                                            </a>
                                        @endif
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
                                            <th class="text-center">{{__('Total')}} ($)</th>
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
                                        <div class="items" id="detaillOrder">
                                        </div>
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
                                    <div class="col-md-12 col-lg-12">
                                        <div id="retir">
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
    <div class="modal fade" id="client-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Client data')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="name" type="text" class="form-control" id="name">
                            <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                            <span id="nameError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="last_name" type="text" class="form-control" id="last_name">
                            <label class="form-label" for="form2Example17">{{__('Last Name')}}</label>
                            <span id="last_nameError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-4 col-sm-4" style="padding-right:0">
                                        <select disabled class="form-select required" name="nationality">
                                            <option value="V">V</option>
                                            <option value="E">E</option>
                                        </select>
                                    </div>
                                    <div class="col-8 col-sm-8" style="padding-left:0">
                                        <input disabled name="ci" type="text" class="form-control" id="ci" >
                                    </div>
                                </div>
                                <label class="form-label" for="form2Example17">{{__('Identification card')}}</label>
                                <span id="ciError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline mb-2">
                            <input disabled name="phone" type="text" class="form-control" id="phone">
                            <label class="form-label" for="form2Example17">{{__('Phone')}}</label>
                            <span id="phoneError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="direction" type="text" class="form-control" id="direction">
                            <label class="form-label" for="form2Example17">{{__('Direction')}}</label>
                            <span id="directionError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="email" type="text" class="form-control" id="email">
                            <label class="form-label" for="form2Example17">{{__('Email')}}</label>
                            <span id="emailError" class="text-danger error-messages"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap verpayment model -->
    <div class="modal fade" id="verpayment-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Payment record')}}:</h5>
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
                                <label class="form-label" for="verreference">{{__('Reference')}}:</label>
                                <input name="verreference" type="text" class="form-control" id="verreference"
                                    disabled>
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
                                <div id="vercapture">
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
    @if (auth()->user()->type == "EMPRESA")
        <!-- boostrap seller model -->
        <div class="modal fade" id="addSeller-modal" aria-hidden="true" >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"> 
                        <h5 class="modal-title" id="modal-title">{{__('Add Seller')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="javascript:void(0)" id="addSellerForm" name="addSellerForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_cart_seller" id="id_cart_seller">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <select  class="form-select" name="seller" id="single-select-field" data-placeholder="{{__('Select seller')}}">
                                        <option></option>  
                                        @if (count($employees) > 0)
                                            <optgroup label="{{__('Employees')}}">
                                                @foreach ($employees as $employee)
                                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->last_name}} C.I: {{$employee->nationality}}-{{$employee->ci}}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if (count($sellers) > 0)
                                            <optgroup label="{{__('Sellers')}}">
                                                @foreach ($sellers as $seller)
                                                    <option value="{{$seller->id}}">{{$seller->name}} {{$seller->last_name}} C.I: {{$seller->nationality}}-{{$seller->ci}}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                    <label class="form-label" for="form2Example17">{{__('Seller')}}</label>
                                    <span id="sellerError" class="text-danger error-messages"></span>
                                </div> 
                            </div>  
                            <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                                <button type="submit" class="btn btn-primary" id="btn-save-seller">{{__('Send')}}</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
        <!-- end bootstrap model -->
    @endif
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
                ajax: "{{ url('ajax-crud-datatableOrder') }}",
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
                    [0, 'desc']
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
                $('#ajax-crud-datatable tbody tr td:nth-child(5)').addClass('text-center ');
            }
            $('#single-select-field' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",  
                dropdownParent: $('#addSeller-modal .modal-body'),
                language: "es"
            }); 
        });

        function updateOrder(id) {
            var quantity = $('#quantityOrder' + id).val();
            if (quantity < 1) {
                $('#quantityOrder' + id).val(1);
                quantity = 1;
            }
            $.ajax({
                type: "POST",
                url: "{{ url('updateOrder') }}",
                data: {
                    'id': id,
                    'quantity': quantity
                },
                dataType: 'json',
                success: function(res) {
                    console.log('carrito id: ' + res)
                    summaryOrder(res)
                }
            });
        }
        function addSellerModal(id) {
            $('#addSeller-modal').modal('show');
            $('#id_cart_seller').val(id);
        }
        $('#addSellerForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');

            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('addSeller') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $('#addSeller-modal').modal('hide');
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Log saved successfully')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#sellerError').html(error.responseJSON.errors.seller);
                    }
                }
            });
        });
        function addAllSeller() {
            $.ajax({
                type: "POST",
                url: "{{ url('addAllSeller') }}",
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    if (res.employee) {
                        Swal.fire({
                            title: "{{__('you do not have employees available')}}",
                            text: "{{__('you do not have employees available')}}",
                            icon: "question"
                        });
                    } else if(res.cart ) {
                        Swal.fire({
                            title: "{{__('you have no orders to assign')}}",
                            text: "{{__('you have no orders to assign')}}",
                            icon: "question"
                        });
                    } else {
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "{{__('assigned jobs')}}",
                            showConfirmButton: false,
                            timer: 1500
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
        }
        function deleteOrder(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('deleteOrder') }}",
                data: {
                    'id': id
                },
                dataType: 'json',
                success: function(res) {
                    console.log('carrito id: ' + res)
                    mostrarOrder(res)
                }
            });
        }

        function mostrarOrder(id) {
            $('#retir').html('');
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
                        $('#order-modal').modal('show');
                        var urls = "'/products/product.png'";
                        if (res.status.status == 'PENDIENTE') {
                            res.data.forEach(element => {
                                $('#detaillOrder').append(
                                    '<div class="product"  >' +
                                    '<div class="row">' +
                                    '<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-4 col-sm-5 col-6 ">' +
                                    '<img class="img-fluid mx-auto d-block image" src="/products/' +
                                    element.url1 + '"  onerror="this.src=' + urls +'">' +
                                    '</div>' +
                                    '<div class="col-xxl-9 col-xl-9 col-lg-9 col-md-8 col-sm-7 col-6">' +
                                    '<div class="info">' +
                                    '<div class="row" >' +
                                    '<div class="col-md-5 product-name">' +
                                    '<div class="product-name">' + element.name + '</div>' +
                                    '</div>' +
                                    '<div class="col-md-3 quantity">' +
                                    '<label for="quantity">{{__('Quantity')}}:</label>' +
                                    '<input id="quantityOrder' + element.id +
                                    '" type="number" onChange="updateOrder(' + element.id +
                                    ')" value ="' + element.quantity +
                                    '" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()">' +
                                    '</div>' +
                                    '<div class="col-md-2 price" style="margin: auto;">' +
                                    '<span  style="font-size: 15px;" >$' + element.price +
                                    '</span>' +
                                    '</div>' +
                                    '<div class="col-md-2 delete" style="margin: auto;">' +
                                    '<a style=" padding: 5px; margin-top: 0.1px !important;" onClick="deleteOrder(' +
                                    element.id +
                                    ')" data-toggle="tooltip" class="delete btn btn-danger">' +
                                    '<i class="fa-solid fa-trash-can"></i>' +
                                    '</a>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>'
                                );
                            });
                            $('#retir').html(
                                '<div class="row">' +
                                '<div class="col-md-4 col-4" style="display: inline-block !important; text-align: right !important;">' +
                                '<label style="font-size: 18px !important; ">{{__('Additional price')}}: </label>' +
                                '</div>' +
                                '<div class="col-md-4 col-4">' +
                                '<input name="additional" type="text" class="form-control" id="additional"  placeholder="{{__('Additional price')}}" title="Es obligatorio un precio" minlength="1" maxlength="10" required onkeypress="return validaMonto(event)" >' +
                                '</div>' +
                                '<div class="col-md-4 col-4">' +
                                '<a class="btn btn-info"  onClick="ajustar(' + id + ')" >{{__('Adjust')}}</a>' +
                                '</div>' +
                                '</div>'
                            );
                        } else {
                            res.data.forEach(element => {
                                $('#detaillOrder').append(
                                    '<div class="product"  >' +
                                    '<div class="row">' +
                                    '<div class="col-md-3">' +
                                    '<img class="img-fluid mx-auto d-block image" src="/products/' +
                                    element.url1 + '"  onerror="this.src=' + urls + '">' +
                                    '</div>' +
                                    '<div class="col-md-9">' +
                                    '<div class="info">' +
                                    '<div class="row" >' +
                                    '<div class="col-md-6 product-name">' +
                                    '<div class="product-name">' +
                                    '<a href="#">' + element.name + '</a>' +
                                    '</div>' +
                                    '</div>' +
                                    '<div class="col-md-3 quantity">' +
                                    '<label for="quantity">{{__('Quantity')}}:</label>' +
                                    '<input id="quantityOrder' + element.id +
                                    '" type="number" onChange="updateOrder(' + element.id +
                                    ')" value ="' + element.quantity +
                                    '" min="1" step="1"   class="form-control quantity-input" onkeydown="filtro()" disabled >' +
                                    '</div>' +
                                    '<div class="col-md-3  price" style="margin: auto;">' +
                                    '<span  style="font-size: 15px;" >$' + element.price +
                                    '</span>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>'
                                );
                            });
                        }
                        summaryOrder(id)
                        if (res.status.status == 'PENDIENTE') {
                            var statusA = "'APROBADO'";
                            var statusR = "'RECHAZADO'";
                            $('#btnOrder').html(
                                '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusOrder(' +
                                id + ',' + statusA + ')" >{{__('Approve')}}</a>' +
                                '<a class="btn btn-danger"  onClick="statusOrder(' + id + ',' + statusR +
                                ')" >{{__('Decline')}}</a>'
                            );
                        } else if (res.status.status == 'APROBADO') {
                            var statusF = "'FINALIZADO'";
                            var statusI = "'INCONCLUSO'";
                            if (res.playment) {
                                $('#btnOrder').html(
                                    '<a class="btn btn-info" style="margin-right: 20px !important;" onClick="verpagoOrder(' +
                                    id + ')" >{{__('See Payment')}}</a>' +
                                    '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusOrder(' +
                                    id + ',' + statusF + ')" >{{__('Finalize')}}</a>' +
                                    '<a class="btn btn-danger"  onClick="statusOrder(' + id + ',' +
                                    statusI + ')" >{{__('Unfinished')}}</a>'
                                );
                            } else {
                                $('#btnOrder').html(
                                    '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusOrder(' +
                                    id + ',' + statusF + ')" >{{__('Finalize')}}</a>' +
                                    '<a class="btn btn-danger"  onClick="statusOrder(' + id + ',' +
                                    statusI + ')" >{{__('Unfinished')}}</a>'
                                );
                            }

                        } else {
                            if (res.playment) {
                                $('#btnOrder').html(
                                    '<a class="btn btn-info" style="margin-right: 20px !important;" onClick="verpagoOrder(' +
                                    id + ')" >{{__('See Payment')}}</a>'
                                );
                            } else {
                                $('#btnOrder').html('');
                            }
                        }
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
                    console.log(res);
                    console.log("res");
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
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function editFunc(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('editClient') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
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

        function statusOrder(id, status) {
            Swal.fire({
                title: "{{__('Are you sure you want to change the status of the order?')}}",
                text: "{{__('The order will be')}} " + {{__('status')}} ,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{__('Yeah!')}}",
                cancelButtonText: "{{__('Cancel')}}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('statusOrder') }}",
                        data: {
                            'id': id,
                            'status': status
                        },
                        dataType: 'json',
                        success: function(res) {
                            console.log(res)
                            $('#order-modal').modal('hide');
                            var oTable = $('#ajax-crud-datatable').dataTable();
                            oTable.fnDraw(false);
                            Swal.fire({
                                title: "{{__('Status change')}}!",
                                text: "{{__('The order was updated.')}}",
                                confirmButtonText: "{{__('Okay')}}",
                                icon: "success"
                            });
                        }
                    });
                }
            });
        }

        function ajustar(id) {
            additional = $('#additional').val();
            $.ajax({
                type: "POST",
                url: "{{ url('ajustar') }}",
                data: {
                    id: id,
                    additional: additional
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    summaryOrder(id);
                }
            });
        }

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
                        '<img  src="/payments/' + res.capture + '"' +
                        'class="card-product-img-top mx-auto"' +
                        'style="height: 150px; width: 150px;display: block;  margin-top: 10px;"' +
                        'alt="capture">'
                    );
                }
            });
        }
    </script>
@endsection
