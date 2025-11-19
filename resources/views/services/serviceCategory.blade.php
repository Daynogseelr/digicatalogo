
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'serviceCategory'])
@section('content')
    <div class="container-fluid">
        <div class="row ">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Service Categories')}}</h4>
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
                                <table class="table table-striped" id="ajax-crud-datatableServiceCategory" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Name')}}</th>
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
    <!-- boostrap serviceCategory model -->
    <div class="modal fade" id="serviceCategory-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Service Categories')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="serviceCategoryForm" name="serviceCategoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="150" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline">
                                <select class="form-select" name="brand" id="brand">
                                    <option value="1">{{__('SI')}}</option>
                                    <option value="0">{{__('NO')}}</option>  
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Brand')}}</label>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline">
                                <select class="form-select" name="model" id="model">
                                    <option value="1">{{__('SI')}}</option>
                                    <option value="0">{{__('NO')}}</option>  
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Model')}}</label>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline">
                                <select class="form-select" name="serial" id="serial">
                                    <option value="1">{{__('SI')}}</option>
                                    <option value="0">{{__('NO')}}</option>  
                                </select>
                                <label class="form-label" for="form2Example17">{{__('Serial')}}</label>
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
            $('#ajax-crud-datatableServiceCategory').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableServiceCategory') }}",
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false},
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
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
                $('#ajax-crud-datatableServiceCategory tbody tr td:nth-child(1)').addClass('text-center');
            }
        });     
        function add(){
            $('#serviceCategoryForm').trigger("reset");
            $("#serviceCategory-modal").modal('show');
            $('#modal-title').html("{{__('Add Service Categories')}}");
            $('#id').val('');
            $('.error-messages').html('');
        }  
        $('#serviceCategoryForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('storeServiceCategory')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#serviceCategory-modal").modal('hide');
                    var oTable = $('#ajax-crud-datatableServiceCategory').dataTable();
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
                        $('#nameError').html(error.responseJSON.errors.name);
                    } 
                }    
            });
        });
        function editFunc(id){
            $.ajax({
                type:"POST",
                url: "{{ url('editServiceCategory') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#modal-title').html("{{__('Edit Service Category')}}");
                    $('.error-messages').html('');
                    $('#serviceCategory-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                }
            });
        }  
        function micheckbox(id){
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id; 
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusServiceCategory') }}",
                data: {'id': id},
                success: function(data){
                    console.log(data);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Modified status')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia'+id+'').html('');
                    if (data.status == '1') {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-on text-success fs-4" style="margin: -6px !important;"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-off text-danger fs-4" style="margin: -6px !important;"></i>');
                    }
                }
            });
        }
    </script>
@endsection

