
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'userCompany'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-4">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-sm-11">
                                        <h4>Empresas</h4>
                                    </div>
                                    <div class="col-sm-1">
                                        <a class="btn btn-primary" onClick="add()" href="javascript:void(0)"> 
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
                                            <th>Nombre</th>
                                            <th>RIF</th>
                                            <th>Telefono</th>
                                            <th>Direccion</th>
                                            <th>Correo</th>
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
    <!-- boostrap company model -->
    <div class="modal fade" id="company-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Agregar Empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="companyForm" name="companyForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="Nombre" title="Es obligatorio un nombre" minlength="2" maxlength="20" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Nombre</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-4" style="padding-right:0" >
                                            <select class="form-select required" name="nationality" >
                                                <option value="J">J</option>	
                                            </select>	
                                        </div>
                                        <div class="col-sm-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control" id="ci"  placeholder="RIF" title="Es obligatorio una cedula" minlength="7" maxlength="10" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">  
                                        </div>
                                    </div>
                                    <label class="form-label" for="form2Example17">RIF</label>
                                    <span id="ciError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="phone"  placeholder="Teléfono" title="Es obligatorio un telefono" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17"> Telefono</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="direction"  placeholder="Direccion" title="Es obligatorio un direccion" minlength="5" maxlength="100" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17"> Direccion</label>
                                <span id="directionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="email" type="text" class="form-control" id="email"  placeholder="Correo" title="Es obligatorio un correo" minlength="5" maxlength="40"required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Correo Electronico</label>
                                <span id="emailError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="password" type="password" class="form-control" id="password" placeholder="Contraseña" title="Es obligatorio una contraseña"  minlength="8" maxlength="20" required >
                                <label class="form-label" for="form2Example27">Contraseña</label>
                                <span id="passwordError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="logo" type="file" class="form-control" id="logo" placeholder="Imagen" title="Es obligatorio una Imagen" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Imagen de la Empresa</label>
                                <span id="logoError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                            <button type="submit" class="btn btn-primary" id="btn-save">Enviar</button>
                        </div>
                    </form>
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
                ajax: "{{ url('ajax-crud-datatable') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'ci', render: function(data, type, row){
                        return `${row.nationality}-${row.ci}`;
                    }},
                    { data: 'phone', name: 'phone' },
                    { data: 'direction', name: 'direction' },
                    { data: 'email', name: 'email' },
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
        function add(){
            $('#companyForm').trigger("reset");
            $('#modal-title').html("Agregar empresa");
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
                    $('#modal-title').html("Editar empresa");
                    $('.error-messages').html('');
                    $('#company-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#ci').val(res.ci);
                    $('#phone').val(res.phone);
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
                        title: "Registro guardado exitosamente",
                        showConfirmButton: false,
                        timer: 1500
                    }); 
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#ciError').html(error.responseJSON.errors.ci);
                        $('#phoneError').html(error.responseJSON.errors.phone);
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
                        title: "Estatus modificado",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia'+id+'').html('');
                    if (data.status == '1') {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-on text-success fs-4"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-off text-danger fs-4"></i>');
                    }
                }
            });
        }
    </script>
@endsection

