
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'serviceClient'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-1">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-12">
                                        <h4>{{__('Tickets')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id="ajax-crud-datatableServiceClient" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Ticket')}}</th>
                                            <th class="text-center">{{__('Company')}}</th>
                                            <th class="text-center">{{__('RIF')}}</th>
                                            <th class="text-center">{{__('Category')}}</th>
                                            <th class="text-center">{{__('Model')}}</th>
                                            <th class="text-center">{{__('Brand')}}</th>
                                            <th class="text-center">{{__('Technician')}}</th>
                                            <th class="text-center">{{__('Estado')}}</th>
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
    <!-- boostrap Solution model -->
    <div class="modal fade" id="approve-modal" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"> 
                    <h5 class="modal-title" id="modal-title">{{__('Solution')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input name="description" type="text" class="form-control" id="descriptionSolution" disabled>
                            <label class="form-label" for="form2Example17">{{__('Description')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input name="solution" type="text" class="form-control" id="solution" placeholder="{{__('Solution')}}" disabled>
                            <label class="form-label" for="form2Example17">{{__('Solution')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="tabla table-responsive" >    
                                <br> 
                                <table  id="dtmodal" class="table table-striped dtmodal">
                                </table>
                            </div>
                        </div>
                    </div>  
                    <div class="col-sm-offset-2 col-sm-12 text-center approve"><br/>
                        
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
                    <h5 class="modal-title" id="modal-title">{{__('Company')}} </h5>
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
    @include('footer')
@endsection  
@section('scripts')
    <script type="text/javascript">
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
            $('#ajax-crud-datatableServiceClient').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableServiceClient') }}",
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'ticker', name: 'ticker' },
                    { data: 'companyName', name: 'companyName' },
                    {
                        data: 'ci',
                        render: function(data, type, row) {
                            return `${row.nationality}-${row.ci}`;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'model', name: 'model' },
                    { data: 'brand', name: 'brand' },
                    {
                        data: 'technicianName',
                        render: function(data, type, row) {
                            if (row.technicianName == null) {
                                return 'Sin asignar';
                            } else {
                                return `${row.technicianName} ${row.technicianLast_name}`;
                            }
                        }
                    },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false},
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                order: [[0, 'desc']],
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="10">10</option>' +
                        '<option value="20">20</option>' +
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
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(5)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(6)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(7)').addClass('text-center');
                $('#ajax-crud-datatableTicket tbody tr td:nth-child(8)').addClass('text-center');
            }
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                dropdownParent: $('#ticket-modal .modal-body'),
                language: "es"
            });
            $('#single-select-technician').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                dropdownParent: $('#addTechnician-modal .modal-body'),
                language: "es"
            });
        });     
        function add(){
            $("#ticket-modal").modal('show');
            $('.error-messages').html('');
        }   
        function mostrarService(id){
            $("#approve-modal").modal('show');
            $('#id_serviceSolution').val(id);
            $('.error-messages').html('');
            $.ajax({
                type:'POST',
                url: "{{ url('mostrarService')}}",
                data: { id: id },
                cache:false,
                dataType: 'json',
                success: (data) => {
                    console.log(data);
                    $('#solution').val(data.service.solution);
                    $('#descriptionSolution').val(data.service.description);
                    if (data.service.status == 'REVISADO') {
                        $('.approve').html(
                            '<a  style="margin-right: 6px !important;" onClick="approveService('+data.service.id+')" title="Aprobar" class="edit btn btn-primary " disabled>Aprobar</a>'+
                            '<a onClick="declineService('+data.service.id+')" title="Rechazar" class="edit btn btn-danger " disabled>Rechazar</a>'
                        );
                    } else {
                        $('.approve').html('');
                    }
                    
                    $('#dtmodal').html('');
                    $('.dtmodal').html(   
                        '<table  id="payment" class="table table-striped">'+           
                            '<thead>'+
                                '<tr style="width:100%; text-align: center; font-size: 12px !important;" >'+
                                    '<th colspan="5" style=" text-align: center;">{{__('Service')}}</th>'+
                                '</tr>'+
                                '<tr style="width:100%; text-align: center; font-size: 12px !important;" >'+
                                    '<th style=" text-align: center;">{{__('Nº')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Name')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Quantity')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Price')}} $</th>'+
                                    '<th style=" text-align: center;" >{{__('Price')}} Bs</th>'+
                                '</tr>'+
                            '</thead>'+
                            '<tbody class="product" style="font-size: 12px !important;">'+                          
                            '</tbody>'+
                        '</table>'
                    );  
                    var  i = 0;
                    $.each(data.serviceDetails, function(index, elemento){
                        i++;
                        $('.product').append(  
                            '<tr>'+
                                '<td style=" text-align: center;">'+i+'</td>'+
                                '<td>'+(elemento.procedure ?? elemento.product_name)+'</td>'+
                                '<td  style=" text-align: center;">'+elemento.quantity+'</td>'+
                                '<td style=" text-align: right;">'+elemento.price+'</td>'+
                                '<td style=" text-align: right;">'+elemento.priceBs+'</td>'+
                            '</tr>'              
                        );  
                    }); 
                    $('.totales').html('');
                    $('.product').append(  
                        '<tr class="totales">'+
                            '<td style=" text-align: center;"></td>'+
                            '<td style=" text-align: center;"></td>'+
                            '<td style=" text-align: right;"><strong>Totales:</strong></td>'+
                            '<td style=" text-align: right;"><strong>'+data.service.price+'</strong></td>'+
                            '<td style=" text-align: right;"><strong>'+data.service.priceBs+'</strong></td>'+
                        '</tr>'              
                    );  
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#solutionError').html(error.responseJSON.errors.solution);
                        $('#priceError').html(error.responseJSON.errors.price);
                    } 
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
        function approveService(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('approveService') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#approve-modal').modal('hide');
                    var oTable = $('#ajax-crud-datatableServiceClient').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    } 
                }    
            });
        }         
        function declineService(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('declineService') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#approve-modal').modal('hide');
                    var oTable = $('#ajax-crud-datatableServiceClient').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    } 
                }   
            });
        }                                                                                            
    </script>
@endsection