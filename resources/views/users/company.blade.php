
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'userCompany'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-1">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Companies')}}</h4>
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
                                <table class="table table-striped" id="ajax-crud-datatable" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Name')}}</th>
                                            <th class="text-center">{{__('Identification card')}}</th>
                                            <th class="text-center">{{__('Phone')}}</th>
                                            <th class="text-center">{{__('Email')}}</th>
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
    <!-- boostrap company model -->
    <div class="modal fade" id="company-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Company')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="companyForm" name="companyForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-4 col-sm-4" style="padding-right:0" >
                                            <select class="form-select required" name="nationality" >
                                                <option value="J">J</option>	
                                            </select>	
                                        </div>
                                        <div class="col-8 col-sm-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control" id="ci"  placeholder="{{__('Identification card')}}" title="Es obligatorio una cedula" minlength="7" maxlength="10" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" autocomplete="off">  
                                        </div>
                                    </div>
                                    <label class="form-label" for="form2Example17">{{__('Identification card')}}</label>
                                    <span id="ciError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="phone"  placeholder="{{__('Phone')}}" title="Es obligatorio un telefono" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Phone')}}</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline mb-2">
                                <input name="state" type="text" class="form-control" id="state"  placeholder="{{__('State')}}" title="Es obligatorio un estado" minlength="3" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('State')}}</label>
                                <span id="stateError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline mb-2">
                                <input name="city" type="text" class="form-control" id="city"  placeholder="{{__('City')}}" title="Es obligatorio un direccion" minlength="3" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('City')}}</label>
                                <span id="cityError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline mb-2"><div class="3"></div>
                                <input name="postal_zone" type="text" class="form-control" id="postal_zone"  placeholder="{{__('Postal Zone')}}" title="Es obligatorio un direccion" minlength="3" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Postal Zone')}}</label>
                                <span id="postal_zoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="direction"  placeholder="{{__('Direction')}}" title="Es obligatorio un direccion" minlength="3" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Direction')}}</label>
                                <span id="directionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="email" type="text" class="form-control" id="email"  placeholder="{{__('Email')}}" title="Es obligatorio un usuario" minlength="2" maxlength="40" required  autocomplete="off" onkeyup="mayus(this);">
                                <label class="form-label" for="form2Example17">{{__('Usuario')}}</label>
                                <span id="emailError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="password" type="password" class="form-control" id="password" placeholder="{{__('Password')}}" title="Es obligatorio una contraseña"  minlength="4" maxlength="20" required >
                                <label class="form-label" for="form2Example27">{{__('Password')}}</label>
                                <span id="passwordError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="logo" type="file" class="form-control" id="logo" placeholder="Imagen" title="Es obligatorio una Imagen" onkeyup="mayus(this);"  autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Company image')}}</label>
                                <span id="logoError" class="text-danger error-messages"></span>
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
            $('#ajax-crud-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatable') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'ci', render: function(data, type, row){
                        return `${row.nationality}-${row.ci}`;
                    }},
                    { data: 'phone', name: 'phone' },
                    { data: 'email', name: 'email' },
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
                $('#ajax-crud-datatable tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(3)').addClass('text-center');
            }
        });
        function add(){
            $('#companyForm').trigger("reset");
            $('#modal-title').html("{{__('Add Company')}}");
            $('.error-messages').html('');
            $('#company-modal').modal('show');
            $('#id').val('');
        }       
        function editFunc(id){
            $.ajax({
                type:"POST",
                url: "{{ url('edit') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#modal-title').html("{{__('Edit Company')}}");
                    $('.error-messages').html('');
                    $('#company-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#slug').val(res.slug);
                    $('#ci').val(res.ci);
                    $('#phone').val(res.phone);
                    $('#state').val(res.state);
                    $('#city').val(res.city);
                    $('#postal_zone').val(res.postal_zone);
                    $('#direction').val(res.direction);
                    $('#email').val(res.email);
                    $('#password').val('PASSWORD');
                }
            });
        }  
        function deleteFuncAp(id){
            var id = id;
            Swal.fire({
                title: "Estas seguro?",
                text: "su registro sera eliminado!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: "Cancelar",
                confirmButtonText: "Si, eliminar!"
                }).then((result) => {
                if (result.isConfirmed) {
                    deleteFunc(id);
                }
            });
        }
        function deleteFunc(id){
            var id = id;
            // ajax
            $.ajax({
                type:"POST",
                url: "{{ url('delete') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        title: "Eliminado!",
                        text: "Su registro fue eliminado.",
                        icon: "success"
                    });
                }
            });
        }
        $('#companyForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('store')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#company-modal").modal('hide');
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    $("#btn-save").html('Enviar');
                    $("#btn-save"). attr("disabled", false);
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
                        $('#slugError').html(error.responseJSON.errors.slug);
                        $('#ciError').html(error.responseJSON.errors.ci);
                        $('#phoneError').html(error.responseJSON.errors.phone);
                        $('#stateError').html(error.responseJSON.errors.state);
                        $('#cityError').html(error.responseJSON.errors.city);
                        $('#postal_zoneError').html(error.responseJSON.errors.postal_zone);
                        $('#directionError').html(error.responseJSON.errors.direction);
                        $('#emailError').html(error.responseJSON.errors.email);
                        $('#passwordError').html(error.responseJSON.errors.password);
                        $('#logoError').html(error.responseJSON.errors.logo);
                    } 
                }    
            });
        });
        function micheckbox(id){
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id; 
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusCompany') }}",
                data: {'id': id},
                success: function(data){
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Modified status')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia'+id+'').html('');
                    if (data.status == '1') {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-on text-success fs-4"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-off text-danger fs-4"></i>');
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
    </script>
@endsection

