
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'guarantee'])
@section('content')
    <div class="container-fluid ">
        <div class="row ">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Guarantees')}}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
                                        <a class="btn btn-danger2" onClick="add()" href="javascript:void(0)"> 
                                            <i class="fa-solid fa-circle-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id="ajax-crud-datatableGuarantee" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Code')}}</th>
                                            <th class="text-center">{{__('Product')}}</th>
                                            <th class="text-center">{{__('Serial')}}</th>
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
    <!-- boostrap add guarantee model -->
    <div class="modal fade" id="addGuarantee-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Guarantee')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="guaranteeForm" name="guaranteeForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <input type="hidden" name="id_guarantee" id="id_guarantee">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <select  class="form-select" name="id_product" id="single-select-field" data-placeholder="{{__('Select product')}}">
                                    <option></option>  
                                    @foreach ($products as $product)
                                        <option value="{{$product->id}}">Cod: {{$product->code}} {{$product->name}}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Product')}}</label>
                                <span id="id_productError" class="text-danger error-messages"></span>
                            </div> 
                             <div class="col-12 col-sm-12 col-md-12" >
                                <select  class="form-select" name="id_inventory" id="inventorySelect">
                                    @foreach ($inventories as $inventory)
                                        <option value="{{$inventory->id}}" >{{$inventory->name}} </option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Inventory')}} </label>
                                <span id="inventoryError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 form-outline">
                                <input class="form-control" id="serial" name="serial" onkeyup="mayus(this);"
                                    autocomplete="off" placeholder="{{__('Serial')}}" ></input>
                                <label class="form-label" for="form2Example17">{{__('Serial')}} (si posee)</label>
                                <span id="serialError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select class="form-select" name="status" id="status">
                                    <option value="REVISION">Revisión</option>
                                    <option value="REPARAR">Reparar a garantia</option>
                                    <option value="DEVOLVER">Devolver a proveedor</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Destino')}}</label>
                            </div> 
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input class="form-control" id="description" name="description" onkeyup="mayus(this);"
                                    autocomplete="off" placeholder="{{__('Description')}}" ></input>
                                <label class="form-label" for="form2Example17">{{__('Description')}}</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                            <button type="submit" class="btn btn-primary" id="btn-save">{{__('Send')}}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap guarantee model -->
    <div class="modal fade" id="guarantee-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Guarantee')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-3 form-outline">
                            <input type="text" class="form-control" id="code2"
                                placeholder="{{__('Code')}} " title="Es obligatorio un codigo"  maxlength="50"
                                onkeypress='return validaMonto(event)' autocomplete="off" disabled>
                            <label class="form-label" for="form2Example17">{{__('Code')}}</label>
                        </div>
                        <div class="col-md-9 col-sm-9 form-outline">
                            <input type="text" class="form-control" id="name2"
                                placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="200"
                                required onkeyup="mayus(this);" autocomplete="off" disabled>
                            <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input class="form-control" id="description2" onkeyup="mayus(this);"
                                autocomplete="off" placeholder="{{__('Description')}}" disabled></input>
                            <label class="form-label" for="form2Example17">{{__('Description')}}</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12" id="inventory">
                        <select  class="form-select" name="id_inventory">
                            @foreach ($inventories as $inventory)
                                <option value="{{$inventory->id}}" >{{$inventory->name}} </option>
                            @endforeach
                        </select>
                        <label class="form-label" for="form2Example17">{{__('Inventory')}} </label>
                    </div>
                    <div class="col-sm-offset-2 col-sm-12 text-center btn-status"><br/>
                        
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
            $('#ajax-crud-datatableGuarantee').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableGuarantee') }}",
                columns: [
                    { data: 'formatted_created_at', name: 'formatted_created_at' },
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    { data: 'serial', name: 'serial' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false},
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                order: [[0, 'desc']],
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
                $('#ajax-crud-datatableGuarantee tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatableGuarantee tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatableGuarantee tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatableGuarantee tbody tr td:nth-child(5)').addClass('text-center');
            }
            $('#single-select-field' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",  
                dropdownParent: $('#addGuarantee-modal .modal-body'),
                language: "es"
            }); 
        });     
        function mostrarGuarantee(id){
            $.ajax({
                type:"POST", 
                url: "{{ url('mostrarGuarantee') }}",
                data:{id:id},
                dataType: 'json',
                success: function(res){
                    $("#guarantee-modal").modal('show');
                    $('#code2').val(res.code);
                    $('#name2').val(res.name);
                    $('#description2').val(res.description);
                    var status = '';
                    document.getElementById("inventory").style.display = "none"; 
                    if (res.status == 'REVISION') {
                        status = 'REPARAR';
                        status2 = 'DEVOLVER';
                        $('.btn-status').html(
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status + '\')" class="btn btn-primary">'+
                                'Reparar'+
                            '</a>'+
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status2 + '\')" class="btn btn-success">'+
                                'Devolver'+
                            '</a>'
                        );
                    } else if (res.status == 'DEVOLVER') {
                        status = 'EMBALADO';
                        $('.btn-status').html(
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status + '\')" class="btn btn-primary">'+
                                'Embalado'+
                            '</a>'
                        );
                    } else if (res.status == 'EMBALADO') {
                        status = 'ENVIADO';
                        $('.btn-status').html(
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status + '\')" class="btn btn-primary">'+
                                'Enviado'+
                            '</a>'
                        );
                    } else if (res.status == 'REPARAR') {
                        status = 'REPARANDO';
                        $('.btn-status').html(
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status + '\')" class="btn btn-primary">'+
                                'Reparando'+
                            '</a>'
                        );
                    } else if (res.status == 'REPARANDO') {
                        status = 'REPARADO';
                        status2 = 'RECICLADO';
                        document.getElementById("inventory").style.display = "block";
                        $('.btn-status').html(
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status + '\')" class="btn btn-primary">'+
                                'Reparado'+
                            '</a>'+
                            '<a href="javascript:void(0)" onClick="statusGuarantee('+res.id+', \'' + status2 + '\')" class="btn btn-success">'+
                                'Reciclado'+
                            '</a>'
                        );
                    } else {
                        $('.btn-status').html('');
                    }
                }
            });
        }  
        function statusGuarantee(id,status){
            $.ajax({
                type:"POST", 
                url: "{{ url('statusGuarantee') }}",
                data:{id:id,status:status},
                dataType: 'json',
                success: function(res){
                    $("#guarantee-modal").modal('hide');
                    var oTable = $('#ajax-crud-datatableGuarantee').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Log saved successfully')}}",
                        showConfirmButton: false,
                        timer: 1500
                    }); 
                }
            });
        }
        function add(){
            $("#addGuarantee-modal").modal('show');
            $('.error-messages').html('');
        }  
        $('#guaranteeForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('storeGuarantee')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#addGuarantee-modal").modal('hide');
                    var oTable = $('#ajax-crud-datatableGuarantee').dataTable();
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
                        $('#descriptionError').html(error.responseJSON.errors.description);
                        $('#id_productError').html(error.responseJSON.errors.id_product);
                    } 
                }    
            });
        });
    </script>
@endsection

