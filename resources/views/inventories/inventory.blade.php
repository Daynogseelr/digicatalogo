@extends('app', ['page' => __('Inventarios'), 'pageSlug' => 'Inventory'])
@section('content')
<style>
    .card-header-info h4 {
        font-weight: 700;
        color: #35b3e5;
        letter-spacing: 1px;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        padding: 4px 8px;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        padding: 4px 8px;
    }
    .table-striped>tbody>tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #e0e0e0 !important;
    }
    .dt-buttons .dt-button {
        background: #ececec !important;
        color: #000000 !important;
        border-radius: 6px !important;
        margin-right: 4px !important;
        border: none !important;
        font-weight: 600 !important;
        padding: 6px 14px !important;
    }
    .dt-buttons .dt-button:hover {
        background: #d1d0d0 !important;
        color: #000000 !important;
    }
</style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Inventories')}}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
                                        <a class="btn btn-danger2" onClick="add()" href="javascript:void(0)">
                                            <i class="fa-solid fa-circle-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="font-size: 13px;">
                            {{-- DataTable --}}
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'style' => 'font-size:13px;'], true) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de inventario -->
    <div class="modal fade" id="inventory-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Inventory')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="inventoryForm" name="inventoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="30" required onkeyup="mayus(this);"  autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="description" type="text" class="form-control" id="description"  placeholder="{{__('Description')}}" title="Es obligatorio una descripcion" minlength="2" maxlength="100" required onkeyup="mayus(this);" autocomplete="off">
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
    @include('footer')
@endsection
@section('scripts')
    {{ $dataTable->scripts() }}
    <script type="text/javascript">
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
        }); 
        function add(){
            $('#inventoryForm').trigger("reset");
            $('#modal-title').html("{{__('Add Inventory')}}");
            $('.error-messages').html('');
            $('#inventory-modal').modal('show');
            $('#id').val('');
        }
        function editFunc(id){
            $.ajax({
                type:"POST",
                url: "{{ url('editInventory') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#modal-title').html("{{__('Edit Inventory')}}");
                    $('.error-messages').html('');
                    $('#inventory-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#description').val(res.description);
                }
            });
        }
        $('#inventoryForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('storeInventory')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#inventory-modal").modal('hide');
                    $('#inventories-table').DataTable().ajax.reload();
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
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
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#descriptionError').html(error.responseJSON.errors.description);
                    }
                }
            });
        });
        function micheckbox(id){
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "{{ url('statusInventory') }}",
                data: {'id': id},
                success: function(data){
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Modified status')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia'+id+'').html('');
                    if (data.status == '1') {
                        $('.cambia'+id+'').append('<i style="margin: -5px !important; padding: 0px !important;"  class="fa-solid fa-toggle-on text-success fs-4"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i style="margin: -5px !important; padding: 0px !important;"  class="fa-solid fa-toggle-off text-danger fs-4"></i>');
                    }
                }
            });
        }
    </script>
@endsection