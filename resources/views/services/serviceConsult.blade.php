
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'serviceConsult'])
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
                                        <h4>{{__('Consult Service')}}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
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
                                <table class="table table-striped" id="ajax-crud-datatableServiceCategory" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
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
        });     
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

