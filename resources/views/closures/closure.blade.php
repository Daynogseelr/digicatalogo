@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'closure'])
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
                                        <h4>{{__('Closures')}}</h4>
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
                        <div class="card-body" >
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="inventory_date">{{ __('Fecha') }}</label>
                                    <input type="date" id="inventory_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="inventory_select">{{ __('Inventario') }}</label>
                                    <select id="inventory_select" class="form-select">
                                        <option value="">{{ __('Seleccionar inventario') }}</option>
                                        @if(isset($inventories))
                                            @foreach($inventories as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button id="btnInventoryClosure" class="btn btn-primary">{{ __('Generar cierre inventario') }}</button>
                                </div>
                            </div>

                            {!! $dataTable->table(['class' => 'table table-striped', 'style' => 'font-size:13px;width:98%!important;'], true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('footer')
@endsection
@section('scripts')
    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
     $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
        function add(){
            Swal.fire({
                title: "{{__('Are you sure about the closure?')}}",
                text: "{{__('closure will be generated!')}}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{__('Yes, generate closure!')}}",
                cancelButtonText: "{{__('Cancel')}}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type:'POST',
                        url: "{{ route('storeClosure') }}",
                        cache:false,
                        contentType: false,
                        processData: false,
                        success: (data) => {
                            if (data != 'mal') {
                                window.LaravelDataTables['global-closure-table'].ajax.reload();
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "{{__('Log saved successfully')}}",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                const pdfLink = "{{ route('pdfClosure', ':id') }}".replace(':id', data);
                                window.open(pdfLink, '_blank');
                            } else {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "info",
                                    title: "{{__('No hay cambios para nuevo cierre')}}",
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
            });
        }
        // Generar cierre de inventario PDF
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btnInventoryClosure').addEventListener('click', function() {
                const date = document.getElementById('inventory_date').value;
                const inventoryId = document.getElementById('inventory_select').value;
                if (!date) {
                    Swal.fire('Error', '{{ __('Selecciona una fecha') }}', 'error');
                    return;
                }
                if (!inventoryId) {
                    Swal.fire('Error', '{{ __('Selecciona un inventario') }}', 'error');
                    return;
                }
                // Abrir en nueva pestaña la ruta que generará el PDF
                const url = '{{ url("/pdfInventory") }}' + '?date=' + encodeURIComponent(date) + '&inventory_id=' + encodeURIComponent(inventoryId);
                window.open(url, '_blank');
            });
        });
    </script>
@endsection