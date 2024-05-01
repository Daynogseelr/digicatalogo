
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'product'])
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
                                        <h4>Productos</h4>
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
                                            <th>Descripcion</th>
                                            <th>Precio</th>
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
    <!-- boostrap product model -->
    <div class="modal fade" id="product-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="productForm" name="productForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="Nombre" title="Es obligatorio un nombre" minlength="2" maxlength="30" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Nombre</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <textarea class="form-control" rows="3" id="description" name="description" onkeyup="mayus(this);" onpaste="return false" autocomplete="off"></textarea>
                                <label class="form-label" for="form2Example17">Descripcion</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-12 form-outline">
                                <input name="price" type="text" class="form-control" id="price"  placeholder="Precio" title="Es obligatorio un precio" minlength="1" maxlength="10" required onkeypress='return validaMonto(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Precio</label>
                                <span id="priceError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-8 col-sm-12 form-outline">
                                <input name="url[]" type="file" class="form-control" id="url" multiple  placeholder="Imagen" title="Es obligatorio una Imagen" onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                <label class="form-label" for="form2Example17">Imagen</label>
                                <span id="urlError" class="text-danger error-messages"></span>
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
                ajax: "{{ url('ajax-crud-datatableProduct') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'price', name: 'price' },
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
        function add(){
            $('#productForm').trigger("reset");
            $('#description').html('');
            $('#modal-title').html("Agregar Producto");
            $('.error-messages').html('');
            $('#product-modal').modal('show');
            $('#id').val('');
        }       
        function editFunc(id){
            $('#productForm').trigger("reset");
            $('#description').html('');
            $.ajax({
                type:"POST",
                url: "{{ url('editProduct') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#modal-title').html("Editar Producto");
                    $('.error-messages').html('');
                    $('#product-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#description').html(res.description);
                    $('#price').val(res.price);
                    $('#url').val(res.url);
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
                url: "{{ url('deleteProduct') }}",
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
        $('#productForm').submit(function(e) {
            e.preventDefault();
            if ($('#url')[0].files.length > 3) {
                Swal.fire({
                    title: "Imagenes?",
                    text: "No se pueden subir mas de tres imagenes",
                    icon: "question"
                });
                $('#url').val('')
            } else {
                $('.error-messages').html('');
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('storeProduct')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#product-modal").modal('hide');
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
                            $('#priceError').html(error.responseJSON.errors.price);
                            $('#descriptionError').html(error.responseJSON.errors.description);
                            $('#urlError').html(error.responseJSON.errors.url);
                        } 
                    }    
                }); 
            } 
            
        });
        function micheckbox(id){
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id; 
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusProduct') }}",
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
                    } else if (data.status == '0') {
                        $('.cambia'+id+'').append('<i class="fa-solid fa-toggle-off text-danger fs-4"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i class="fa-regular fa-circle-dot text-warning fs-4"></i>'); 
                    }
                }
            });
        }
    </script>
@endsection

