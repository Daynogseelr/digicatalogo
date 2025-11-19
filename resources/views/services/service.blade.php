@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'tickets'])
@section('content')
<div class="container-fluid py-1">
    <div class="row">
        @foreach([
            ['Recibido', $countServiceRecibido],
            ['Revisado', $countServiceRevisado],
            ['Terminado', $countServiceTerminado],
            ['Entregado', $countServiceEntregado]
        ] as [$estado, $count])
        <div class="col-xl-3 col-sm-6 col-6 mb-xl-0 mb-4">
            <div class="card card-status-filter" data-status="{{ $estado }}">
                <div class="card-body p-2 p-sm-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold" style="color: black !important;">
                                    {{ __($estado) }}</p>
                                <h5 class="font-weight-bolder">
                                    {{ $count }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape btn-danger text-center rounded-circle">
                                <i class="fa-solid fa-screwdriver-wrench text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-10 col-sm-11">
                                    <h4>{{ __('Tickets') }}</h4>
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
                    <div class="card-body">
                        {!! $dataTable->table(['class' => 'table table-striped', 'style' => 'font-size:13px;width:98%!important;'], true) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap Ticket model -->
    <div class="modal fade" id="ticket-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Ticket') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="ticketForm" name="ticketForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="row">
                                    <div class="col-10 col-sm-10">
                                        <select class="form-select" name="client" id="single-select-field"
                                            data-placeholder="{{ __('Select client') }}">
                                            <option></option>
                                            @foreach ($clients as $client)
                                                <option value="{{$client->id}}" @if(isset($id_shopper) && $id_shopper == $client->id) selected @endif>{{$client->name}} {{$client->last_name}} {{$client->nationality}}-{{$client->ci}}</option>
                                            @endforeach
                                        </select>
                                        <label class="form-label" for="form2Example17">{{ __('Client') }}</label>
                                        <span id="clientError" class="text-danger error-messages"></span>
                                    </div>
                                    <div class="col-2 col-sm-2">
                                        <a class="btn btn-primary" onClick="addClient()" href="javascript:void(0)"
                                            style="padding: 10px !important;">
                                            <i class="fa-solid fa-circle-plus"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <select class="form-select" name="category" id="single-select">
                                    <option value="">{{ __('Select category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Category') }}</label>
                                <span id="categoryError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 form-outline" id="brand-form-group" style="display: none;">
                                <input name="brand" type="text" class="form-control" id="brand"
                                    placeholder="{{ __('Brand') }}" title="Es obligatorio un marca" minlength="2"
                                    maxlength="200" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Brand') }}</label>
                                <span id="brandError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 form-outline" id="model-form-group" style="display: none;">
                                <input name="model" type="text" class="form-control" id="model"
                                    placeholder="{{ __('Model') }}" title="Es obligatorio un modelo" minlength="2"
                                    maxlength="200" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Model') }}</label>
                                <span id="modelError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline" id="serial-form-group" style="display: none;">
                                <input name="serial" type="text" class="form-control" id="serial"
                                    placeholder="{{ __('Serial') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="200" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Serial') }}</label>
                                <span id="serialError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="description" type="text" class="form-control" id="description"
                                    placeholder="{{ __('Description') }}" title="Es obligatorio un nombre"
                                    minlength="2" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Description') }}</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary" id="btn-save">{{ __('Send') }}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap Technician model -->
    <div class="modal fade" id="addTechnician-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Technician') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="addTechnicianForm" name="addTechnicianForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_service" id="id_service">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <select class="form-select" name="technician" id="single-select-technician"
                                    data-placeholder="{{ __('Select Technician') }}">
                                    @if (count($technicians) > 0)
                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}">
                                                {{ $technician->name }} {{ $technician->last_name }} 
                                                (CI: {{ $technician->nationality }}-{{ $technician->ci }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Technician') }}</label>
                                <span id="technicianError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <select class="js-example-basic-multiple" multiple="multiple" name="technician2[]" id="js-example-basic-multiple-technician"
                                    data-placeholder="{{ __('Seleccione Ayudante') }}" disabled>
                                    @if (count($technicians) > 0)
                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}">
                                                {{ $technician->name }} {{ $technician->last_name }} 
                                                (CI: {{ $technician->nationality }}-{{ $technician->ci }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Technician') }}</label>
                                <span id="technician2Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="dt-technician">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary"
                                id="btn-save-Technician">{{ __('Send') }}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
  <!-- Modal Agregar Solución -->
<div class="modal fade" id="addSolution-modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Agregar Solución') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="addSolutionForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_serviceSolution" id="id_serviceSolution">
                    <input type="hidden" name="total" id="total">
                    <div class="row g-2">
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Descripción del Servicio') }}</label>
                            <input name="description" type="text" class="form-control" id="descriptionSolution" disabled>
                            <span id="descriptionError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Solución') }}</label>
                            <input name="solution" type="text" class="form-control" id="solution"
                                placeholder="{{ __('Solución') }}" minlength="2" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                            <span id="solutionError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{__('Pago en Moneda')}}</label>
                            <select class="form-select" id="select-currency" name="currency">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" data-rate="{{ $currency->rate }}">{{ $currency->name }} ({{ $currency->abbreviation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('Procedimiento') }}</label>
                            <div class="input-group">
                                <input name="procedure" type="text" class="form-control" id="procedure"
                                    placeholder="{{ __('Procedimiento') }}" minlength="2" maxlength="200" onkeyup="mayus(this);" autocomplete="off">
                                <input name="price" type="text" class="form-control" id="price"
                                    placeholder="{{ __('Precio') }}" minlength="1" maxlength="10" onkeypress='return validaMonto(event)' autocomplete="off" style="max-width: 120px;">
                                <button class="btn btn-primary" type="button" onClick="addProcedure()">
                                    <i class="fa-solid fa-circle-plus"></i>
                                </button>
                            </div>
                            <span id="procedureError" class="text-danger error-messages"></span>
                            <span id="priceError" class="text-danger error-messages"></span>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">{{__('Productos')}}</label>
                            <select class="js-example-basic-multiple" id="js-example-basic-multiple-products" name="id_product[]" multiple="multiple" data-placeholder="Seleccione un producto">
                                @foreach ($products as $product)
                                    <option value="{{$product->id}}" data-type="{{$product->type}}">{{$product->code}} {{$product->name}}</option>
                                @endforeach
                            </select>
                            <span id="id_productError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive mt-3">
                                <table id="dtmodal" class="table table-striped dtmodal">
                                    <thead>
                                        <tr>
                                            <th colspan="5" style="text-align: center;">{{__("Service")}}</th>
                                            <th class="delete" style=" text-align: center;">
                                        
                                            </th>
                                        </tr>
                                        <tr>
                                            <th style="text-align: center;">{{__("Nº")}}</th>
                                            <th style="text-align: center;">{{__("Name")}}</th>
                                            <th style="text-align: center;">{{__("Tipo")}}</th>
                                            <th style="text-align: center;">{{__("Quantity")}}</th>
                                            <th style="text-align: right;">{{__("Price")}}</th>
                                            <th style="text-align: right;">
                                                <span id="price-moneda-label">{{__("Price")}}</span>
                                                <select id="select-reference-currency" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{ $currency->id }}" data-rate="{{ $currency->rate }}">{{ $currency->abbreviation }}</option>
                                                    @endforeach
                                                </select>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="product" style="font-size: 12px !important;"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3 btn-solution"></div>
                </form>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<!-- Fin modal agregar solución -->
    <!-- boostrap client model -->
    <div class="modal fade" id="client-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Client data') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="name" type="text" class="form-control" id="name">
                            <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                            <span id="nameError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline">
                            <input disabled name="last_name" type="text" class="form-control" id="last_name">
                            <label class="form-label" for="form2Example17">{{ __('Last Name') }}</label>
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
                                        <input disabled name="ci" type="text" class="form-control"
                                            id="ci">
                                    </div>
                                </div>
                                <label class="form-label" for="form2Example17">{{ __('Identification card') }}</label>
                                <span id="ciError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 form-outline mb-2">
                            <input disabled name="phone" type="text" class="form-control" id="phone">
                            <label class="form-label" for="form2Example17">{{ __('Phone') }}</label>
                            <span id="phoneError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="direction" type="text" class="form-control" id="direction">
                            <label class="form-label" for="form2Example17">{{ __('Direction') }}</label>
                            <span id="directionError" class="text-danger error-messages"></span>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline mb-2">
                            <input disabled name="email" type="text" class="form-control" id="email">
                            <label class="form-label" for="form2Example17">{{ __('Email') }}</label>
                            <span id="emailError" class="text-danger error-messages"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap Solution model -->
    <div class="modal fade" id="mostrarService-modal" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"> 
                    <h5 class="modal-title" id="modal-title">{{__('Solución')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input name="mostrardescription" type="text" class="form-control" id="mostrardescription" disabled>
                            <label class="form-label" for="form2Example17">{{__('Description')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <input name="mostrarsolution" type="text" class="form-control" id="mostrarsolution" placeholder="{{__('Solution')}}" disabled>
                            <label class="form-label" for="form2Example17">{{__('Solución')}}</label>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="tabla table-responsive" >    
                                <br> 
                                <table  id="mostrardtmodal" class="table table-striped mostrardtmodal">
                                </table>
                            </div>
                        </div>
                    </div>  
                    <div class="col-sm-offset-2 col-sm-12 text-center mostrarapprove">
                        
                    </div>
                    
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
     <!-- boostrap client model -->
     <div class="modal fade" id="addClient-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Client')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form"  action="{{ route('storeShopperService') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="addName" value="{{ old('name') }}"
                                    placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="250"
                                    required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline">
                                <input name="last_name" type="text" class="form-control" id="addLast_name" value="{{ old('last_name') }}"
                                    placeholder="{{__('Last Name')}}" title="Es obligatorio un apellido" minlength="2" maxlength="100"
                                    onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Last Name')}} <span>{{__('(If it is a company, do not include last name)')}}</span> </label>
                                @error('last_name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-4 col-sm-4" style="padding-right:0">
                                            <select class="form-select required" name="nationality">
                                                <option value="V">V</option>
                                                <option value="E">E</option>
                                                <option value="J">J</option>
                                                <option value="G">G</option>
                                            </select>
                                        </div>
                                        <div class="col-8 col-sm-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control" id="addCi" value="{{ old('ci') }}"
                                                placeholder="{{__('Identification Document')}}" title="Es obligatorio una cedula" minlength="7"
                                                maxlength="10" required onkeypress='return validaNumericos(event)'
                                                onkeyup="mayus(this);" autocomplete="off">
                                        </div>
                                    </div>
                                    <label class="form-label" for="form2Example17">{{__('Identification Document')}}</label>
                                    @error('ci')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="addPhone" value="{{ old('phone') }}"
                                    placeholder="{{__('Phone')}}" title="Es obligatorio un telefono" 
                                    maxlength="11" onkeypress='return validaNumericos(event)'
                                    autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{__('Phone')}}</label>
                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="addDirection" value="{{ old('direction') }}"
                                    placeholder="{{__('Direction')}}" title="Es obligatorio un direccion" 
                                    maxlength="200" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{__('Direction')}}</label>
                                @error('direction')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="email" type="text" class="form-control" id="addEmail" value="{{ old('email') }}"
                                    placeholder="{{__('Email')}}" title="Es obligatorio un correo"
                                    maxlength="50" autocomplete="off" onkeyup="mayus(this);">
                                <label class="form-label" for="form2Example17">{{__('Email')}}</label>
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
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
{!! $dataTable->scripts() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.card-status-filter').on('click', function() {
                var status = $(this).data('status');
                window.LaravelDataTables['service-table'].column(6).search(status).draw();
            });

            // Si quieres limpiar el filtro al hacer doble click en cualquier card
            $('.card-status-filter').on('dblclick', function() {
                window.LaravelDataTables['service-table'].column(6).search('').draw();
            });
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: function() {
                    return $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style';
                },
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                dropdownParent: $('#ticket-modal .modal-body'),
                language: "es",
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                        var modifiedData = $.extend({}, data, true);
                        return modifiedData;
                    }
                    return null;
                }
            }).on('select2:select', function (e) { // Manejar el evento select2:select
                // Se ejecuta cuando se selecciona un elemento, ya sea con el mouse o con Enter.
                console.log("Elemento seleccionado:", e.params.data);
            }).on('select2:opening', function() { // Usar select2:opening para evitar problemas de enfoque
                // Eliminar el manejador de eventos anterior para evitar duplicados
                $('.select2-search__field').off('keydown'); 
            }).on('select2:open', function(e) {
                let $select = $(this);
                $('.select2-search__field').on('keydown', function(event) {
                    if (event.key === 'Enter') {
                        let searchTerm = $(this).val();
                        if (searchTerm.trim() !== "") {
                            let $match = $select.find('option').filter(function() {
                                return $(this).text().toUpperCase() === searchTerm.toUpperCase();
                            });
                            if ($match.length > 0) {
                                // SE ENCONTRÓ UNA COINCIDENCIA
                                console.log("Se encontró una coincidencia:", $match.val());
                                $select.val($match.val()).trigger('change'); // Seleccionar la opción coincidente
                                $select.select2('close'); // Cerrar el dropdown
                                event.preventDefault(); // Evitar el comportamiento predeterminado del Enter
                                event.stopPropagation();
                                return false;
                            } else {
                                // NO SE ENCONTRÓ COINCIDENCIA
                                console.log("No se encontró coincidencia");
                                $('#addCi').val(searchTerm);
                                $('#addClient-modal').modal('show');
                                $('#addClient-modal').on('shown.bs.modal', function (e) {
                                    $('#addName').focus();
                                });
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                            }
                        }
                    }
                });
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
            $('#js-example-basic-multiple-products').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                closeOnSelect: false,
                selectionCssClass: "form-select",  
                dropdownParent: $('#addSolution-modal .modal-body'),
                language: "es"
            });
            $('#js-example-basic-multiple-technician').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#addTechnician-modal .modal-body'),
                language: "es"
            });
            
            // Evento submit del formulario   
            @if(isset($id_shopper))
                var idShopper = @json($id_shopper);
                $('#ticket-modal').modal('show');
            @endif  
        });

        function actualizarTecnicosAyudantes(tecnicoPrincipalId, tecnicosAyudantesIds, secondaryTechnicians, limpiarSeleccion = true) {
    console.log('actualizarTecnicosAyudantes:', tecnicoPrincipalId, tecnicosAyudantesIds, secondaryTechnicians, limpiarSeleccion);

    // Deshabilitar/habilitar las opciones del select de ayudantes directamente
    $('#js-example-basic-multiple-technician option').each(function() {
        if ($(this).val() === tecnicoPrincipalId) {
            $(this).prop('disabled', true);
        } else {
            $(this).prop('disabled', false);
        }
    });

    // Habilitar/deshabilitar el select de ayudantes
    $('#js-example-basic-multiple-technician').prop('disabled', !tecnicoPrincipalId);

    // Limpiar la selección de ayudantes si se indica y usar Select2 para limpiar visualmente
    if (limpiarSeleccion) {
        $('#js-example-basic-multiple-technician').select2('data', null); // Limpiar datos y visualización
    }

    // Limpiar la tabla de ayudantes
    $('.dt-technician').empty();

    // Llenar la tabla si hay ayudantes seleccionados
    if (tecnicosAyudantesIds && tecnicosAyudantesIds.length > 0) {
        var $tablaTecnicos = $('.dt-technician');
        var tablaHTML = '<table class="table">';
        tablaHTML += '<thead><tr><th>Nro</th><th>Nombre</th><th>Porcentaje</th></tr></thead><tbody>';
        var i = 0;
        tecnicosAyudantesIds.forEach(function(tecnicoId) {
            var nombreTecnico = $('#js-example-basic-multiple-technician option[value="' + tecnicoId + '"]').text();
            i++;
            tablaHTML += '<tr>';
            tablaHTML += '<td>' + i + '</td>';
            tablaHTML += '<td>' + nombreTecnico + '</td>';
            tablaHTML += '<td><input type="text" class="form-control porcentaje-tecnico" data-tecnico-id="' + tecnicoId + '" onkeypress="return validaMonto(event)" style="width: 100px;"></td>';
            tablaHTML += '</tr>';
        });
        tablaHTML += '</tbody></table>';
        $tablaTecnicos.append(tablaHTML);
    }

    // Llenar la tabla con porcentajes si vienen de la respuesta AJAX
    if (secondaryTechnicians && secondaryTechnicians.length > 0) {
        var $tablaTecnicos = $('.dt-technician');
        $tablaTecnicos.empty();
        var tablaHTML = '<table class="table">';
        tablaHTML += '<thead><tr><th>Nro</th><th>Nombre</th><th>Porcentaje</th></tr></thead><tbody>';
        var i = 0;
        secondaryTechnicians.forEach(function(technician) {
            i++;
            tablaHTML += '<tr>';
            tablaHTML += '<td>' + i + '</td>';
            tablaHTML += '<td>' + technician.name + ' ' + technician.last_name + ' (CI: ' + technician.nationality + '-' + technician.ci + ')</td>';
            tablaHTML += '<td><input type="text" class="form-control porcentaje-tecnico" data-tecnico-id="' + technician.id + '" value="' + technician.percent + '" onkeypress="return validaMonto(event)" style="width: 100px;"></td>';
            tablaHTML += '</tr>';
        });
        tablaHTML += '</tbody></table>';
        $tablaTecnicos.append(tablaHTML);
    }
}

$('#single-select-technician').on('change', function() {
    console.log('single-select-technician change');
    var tecnicoPrincipalId = $(this).val();
    $('#js-example-basic-multiple-technician').val('').trigger('change');
});

// Evento change del select de ayudantes
$('#js-example-basic-multiple-technician').off('change').on('change', function() {
    console.log('js-example-basic-multiple-technician change');
    var tecnicosAyudantesIds = $(this).val();
    actualizarTecnicosAyudantes($('#single-select-technician').val(), tecnicosAyudantesIds, null, false); // No limpiar selección aquí
});

function addTechnicianModal(id) {
    console.log('addTechnicianModal');
    $("#addTechnician-modal").modal('show');
    $('#id_service').val(id);
    $('.error-messages').html('');

    $.ajax({
        url: "{{ url('getTechnicianService') }}/" + id,
        type: "GET",
        success: function(response) {
            if (response.technician) {
                $('#single-select-technician').val(response.technician.id).trigger('change'); // Actualizar ayudantes
            } else {
                $('#single-select-technician').val('').trigger('change'); // Actualizar ayudantes
            }

            if (response.secondary_technicians) {
                var selectedTechnicians = response.secondary_technicians.map(function(technician) {
                    return technician.id.toString();
                });
                $('#js-example-basic-multiple-technician').val(selectedTechnicians).trigger('change'); // Actualizar visualización Select2
                actualizarTecnicosAyudantes($('#single-select-technician').val(), selectedTechnicians, response.secondary_technicians);
            } else {
                $('#js-example-basic-multiple-technician').select2('data', null); // Limpiar datos y visualización
                actualizarTecnicosAyudantes($('#single-select-technician').val(), null, null); // Actualizar tabla
                $('#js-example-basic-multiple-technician').empty(); // Limpiar opciones
                // Recargar opciones
                @if (count($technicians) > 0)
                    @foreach ($technicians as $technician)
                        $('#js-example-basic-multiple-technician').append('<option value="{{ $technician->id }}">{{ $technician->name }} {{ $technician->last_name }} (CI: {{ $technician->nationality }}-{{ $technician->ci }})</option>');
                    @endforeach
                @endif
            }
        },
        error: function(error) {
            console.error(error);
        }
    });
}
        function add() {
            $('#ticketForm').trigger("reset");
            $("#ticket-modal").modal('show');
            $('.error-messages').html('');
        }
        function addClient() {
            $('#clientForm').trigger("reset");
            $('.error-messages').html('');
            $('#addClient-modal').modal('show');
            console.log('data');
        }
        
        function addSolution(id) {
            $("#addSolution-modal").modal('show');
            $('#id_serviceSolution').val(id);
            $('.error-messages').html('');
            $.ajax({
                type: 'POST',
                url: "{{ url('editSolution') }}",
                data: {
                    id: id
                },
                cache: false,
                dataType: 'json',
                success: (data) => {
                    console.log(data);
                     $('#solution').val(data.service.solution);
                    $('#descriptionSolution').val(data.service.description);

                    // Seleccionar la moneda del servicio
                    if (data.service.id_currency) {
                        $('#select-currency').val(data.service.id_currency).trigger('change');
                    } else {
                        $('#select-currency').val($('#select-currency option:first').val()).trigger('change');
                    }

                    if (data.id_product) { // Verifica si data.id_product existe
                        $('.js-example-basic-multiple').val(data.id_product).trigger('change');
                    } else {
                        // Si data.id_product no existe, limpia la selección en Select2
                        $('.js-example-basic-multiple').val(null).trigger('change');
                    }
                    if (data.service.status == 'REVISIÓN') {
                        $('.btn-solution ').html(' <button type="submit" class="btn btn-primary" id="btn-save-Technician">{{ __('Send') }}</button>');
                    } else if (data.service.status == 'REVISADO' || data.service.status == 'APROBADO' || data.service.status == 'RECHAZADO') {
                        $('.btn-solution ').html(
                            ' <button type="submit" class="btn btn-primary" id="btn-save-Technician">{{ __('Send') }}</button>'+
                            '<a  style="margin-left: 6px !important;" onClick="endService('+data.service.id+')" title="Terminar" class="edit btn btn-success " disabled>Terminar</a>'
                        );                        
                    }  
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
        $('#ticketForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeService') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (id) => {
                    $("#ticket-modal").modal('hide');
                     $('#services-table').DataTable().ajax.reload();
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    console.log('id_service'+id);
                    const pdfLink = "{{ route('pdfTicket', ':id') }}".replace(':id', id);
                    window.open(pdfLink, '_blank');
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#clientError').html(error.responseJSON.errors.client);
                        $('#categoryError').html(error.responseJSON.errors.category);
                        $('#modelError').html(error.responseJSON.errors.model);
                        $('#brandError').html(error.responseJSON.errors.brand);
                        $('#descriptionError').html(error.responseJSON.errors.description);
                    }
                }
            });
        });
        $('#addTechnicianForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var tecnicoPrincipalId = $('#single-select-technician').val();
            var tecnicosAyudantesIds = $('#js-example-basic-multiple-technician').val();
            var id_service = $('#id_service').val();
            var porcentajes = {};
            var errores = false;
            var sumaPorcentajes = 0;

            $('.porcentaje-tecnico').each(function() {
                var tecnicoId = $(this).data('tecnico-id');
                var porcentaje = $(this).val();
                if (!porcentaje) {
                    alert('Por favor, ingrese el porcentaje para todos los técnicos ayudantes.');
                    errores = true;
                    return false; // Detener el bucle each
                }
                sumaPorcentajes += parseInt(porcentaje);
                porcentajes[tecnicoId] = porcentaje;
            });

            if (errores) {
                return; // Detener el envío del formulario si hay errores
            }

            if (sumaPorcentajes > 100) {
                alert('La suma de los porcentajes no puede ser mayor a 100%.');
                return; // Detener el envío del formulario si la suma excede 100
            }
            $.ajax({
                type: 'POST',
                url: "{{ url('storeTechnician') }}",
                data: {
                    id_technician: tecnicoPrincipalId,
                    tecnicosAyudantesIds: tecnicosAyudantesIds,
                    porcentajes: porcentajes,
                    id_service: id_service,
                },
                success: (data) => {
                    $("#addTechnician-modal").modal('hide');
                     $('#services-table').DataTable().ajax.reload();
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
                        $('#technicianError').html(error.responseJSON.errors.id_technician);
                    }
                }
            });
        });
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
        $(document).on('change', '#js-example-basic-multiple-products', function(event){
            var id_service = $('#id_serviceSolution').val();
            $.ajax({
                type: "POST",
                url: "{{ url('addProduct') }}",
                data:{ 
                    id_product: $('#js-example-basic-multiple-products').val(),
                    id_service: id_service
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res)
                    tableProductService();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        });
        function tableTotalService() {
    let total = 0;
    let totalMoneda = 0;

    // Obtén el rate de la moneda seleccionada
    let currency_rate = $('#select-currency option:selected').data('rate') || 1;

    // Recorre las filas de la tabla y suma los precios
    $('.product tr').each(function() {
        // Obtén el precio en $ (columna 5)
        let priceText = $(this).find('td').eq(4).text();
        let price = parseFloat(priceText.replace(/,/g, '')) || 0;
        total += price;

        // Obtén el precio en moneda (columna 6)
        let priceMonedaText = $(this).find('td').eq(5).text();
        let priceMoneda = parseFloat(priceMonedaText.replace(/,/g, '')) || 0;
        // Si la columna está vacía, calcula usando el rate
        if (!priceMoneda && price) {
            priceMoneda = price * currency_rate;
        }
        totalMoneda += priceMoneda;
    });

    // Elimina cualquier fila de totales previa
    $('.totales').remove();

    // Agrega la fila de totales al final de la tabla
    $('.product').append(
        '<tr class="totales">' +
            '<td style="text-align: center;"></td>' +
            '<td style="text-align: center;"></td>' +
            '<td style="text-align: center;"></td>' +
            '<td style="text-align: right;"><strong>Totales:</strong></td>' +
            '<td style="text-align: right;"><strong>' + total.toFixed(2) + '</strong></td>' +
            '<td style="text-align: right;"><strong>' + totalMoneda.toFixed(2) + '</strong></td>' +
        '</tr>'
    );

    // Actualiza los inputs ocultos
    $('#total').val(total.toFixed(2));
}
        function tableProductService() {
     var id_service = $('#id_serviceSolution').val();
    var currency_id = $('#select-currency').val();
    var currency_rate = $('#select-currency option:selected').data('rate');
    var reference_currency_id = $('#select-reference-currency').val();
    var reference_currency_rate = $('#select-reference-currency option:selected').data('rate');

    $.ajax({
        type: "POST",
        url: "{{ url('tableProductService') }}",
        data: {
            id_product: $('.js-example-basic-multiple').val(),
            id_service: id_service,
            currency_id: currency_id,
            currency_rate: currency_rate
        },
        dataType: 'json',
        success: function(res) {
            $('.product').html('');
            $('.delete').html(  '<a style=" padding: 4px; margin-bottom: -4px !important;  font-size: 11px !important;" onClick="deleteServiceDetail('+
                                    id_service +
                                        ')" data-toggle="tooltip" class="btn btn-danger">'+
                                        '<i class="fa-solid fa-trash-can"></i>'+
                                '</a>');
            var i = 0;
            $.each(res.serviceDetails, function(index, elemento) {
                i++;
                let modeSelect = '';
                if (elemento.type === 'FRACCIONADO') {
                    modeSelect = `
                        <select class="form-select form-select-sm mode-fraction" data-id="${elemento.id}" style="width:110px;display:inline-block;">
                            <option value="COMPLETO" ${elemento.mode === 'COMPLETO' ? 'selected' : ''}>Completo</option>
                            <option value="FRACCION" ${elemento.mode === 'FRACCION' ? 'selected' : ''}>Fracción</option>
                        </select>
                    `;
                }
                // Precio en moneda de referencia
                let priceMoneda = elemento.price * reference_currency_rate;
                $('.product').append(
                    '<tr>' +
                    '<td style="text-align: center;">' + i + '</td>' +
                    '<td>' + elemento.name + (modeSelect ? '<br>' + modeSelect : '') + '</td>' +
                    '<td style="text-align: center;">' + elemento.type + '</td>' +
                    '<td style="text-align: center;">' +
                        '<input id="quantity' + elemento.id +
                        '" type="text" onChange="updateQuantityService(' + elemento.id +
                        ')" value ="' + elemento.quantity +
                        '" min="1" step="1" data-id="' + elemento.id +
                        '" style="width: 40px !important;" class="quantity-input quantity" onkeypress="return validaNumericos(event)">' +
                    '</td>' +
                    '<td style="text-align: right;">' + elemento.price + '</td>' +
                    '<td style="text-align: right;">' + priceMoneda.toFixed(2) + '</td>' +
                    '</tr>'
                );
            });
            tableTotalService();
        },
        error: function(error) {
            console.log(error);
        }
    });
}
$(document).on('change', '#select-currency', function() {
    let id_service = $('#id_serviceSolution').val();
    let id_currency = $(this).val();
    let rate_new = $('#select-currency option:selected').data('rate');
    // AJAX para actualizar moneda y precios en el backend
    $.ajax({
        type: "POST",
        url: "{{ url('updateServiceCurrency') }}",
        data: {
            id_service: id_service,
            id_currency: id_currency,
            rate_new: rate_new
        },
        dataType: 'json',
        success: function(res) {
            tableProductService();
        },
        error: function(error) {
            console.log(error);
        }
    });
});

// Al cambiar la moneda de referencia, solo actualiza la columna de precios en moneda
$(document).on('change', '#select-reference-currency', function() {
    tableProductService();
});

// Actualizar modo de venta fraccionado
$(document).on('change', '.mode-fraction', function() {
    let serviceDetailId = $(this).data('id');
    let mode = $(this).val();
    $.ajax({
        type: "POST",
        url: "{{ url('updateFractionModeService') }}",
        data: {
            id: serviceDetailId,
            mode: mode
        },
        dataType: 'json',
        success: function(res) {
            tableProductService();
        },
        error: function(error) {
            console.log(error);
            console.log(error.responseJSON.errors);
            Swal.fire('Error', 'No se pudo actualizar el modo de venta.', 'error');
        }
    });
});

        function addProcedure() {
            $('.error-messages').html('');
            var procedure = $('#procedure').val();
            var price = $('#price').val();
            var id_service = $('#id_serviceSolution').val();
            $.ajax({
                type: "POST",
                url: "{{ url('addProcedure') }}",
                data:{ 
                    procedure: procedure, 
                    price: price, 
                    id_service: id_service, 
                },
                dataType: 'json',
                success: function(res) {
                    $('#procedure').val('');
                    $('#price').val('');
                    $('.js-example-basic-multiple').trigger('change');
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#procedureError').html(error.responseJSON.errors.procedure);
                        $('#priceError').html(error.responseJSON.errors.price);
                    }
                }
            });
        }
        function updateQuantityService(id) {
            var quantity = $('#quantity'+id).val();
            if (quantity < 1) {
                quantity = 1;
            } 
            $.ajax({
                type: "POST",
                url: "{{ url('updateQuantityService') }}",
                data: {
                    id: id,
                    quantity: quantity,
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    tableProductService();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }
        window.addEventListener('keydown', function(event) {
            if (event.keyCode === 13) {
                var inputsquantity = document.getElementsByClassName('quantity'); 
                console.log('Se presionó Enter en cualquier parte de la página');
                for (let i = 0; i < inputsquantity.length; i++) {
                    inputsquantity[i].addEventListener('keydown', function(event) {
                        if (event.keyCode === 13) {
                            // Este es el input al que se le presionó Enter
                            const inputPresionado = event.target;
                            console.log('El valor del input presionado es:', inputPresionado.value);
                            var quantity = inputPresionado.value;
                            var id = inputPresionado.dataset.id;
                            if (quantity < 1) {
                                quantity = 1
                                $('#quantity'+id).val(1);
                            } 
                            $.ajax({
                                type: "POST",
                                url: "{{ url('updateQuantityService') }}",
                                data: {
                                    id: id,
                                    quantity: quantity,
                                },
                                dataType: 'json',
                                success: function(res) {
                                    console.log(res);
                                    tableProductService();
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
            }
        });
        $('#addSolutionForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]); // Muestra la clave y el valor
            }
            $.ajax({
                type: 'POST',
                url: "{{ url('storeSolution') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#addSolution-modal").modal('hide');
                     $('#services-table').DataTable().ajax.reload();
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
                        $('#solutionError').html(error.responseJSON.errors.solution);
                    }
                }
            });
        });
        function deleteServiceDetail(id_service) {
            $.ajax({
                type: "POST",
                url: "{{ url('deleteServiceDetail') }}",
                data: {
                    id_service: id_service,
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    $('.js-example-basic-multiple').trigger('change');
                    tableProductService();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }
        function endService(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('endService') }}",
                data: { 
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $("#addSolution-modal").modal('hide');
                     $('#services-table').DataTable().ajax.reload();
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
        function handService(id) {
            var code = $('#code').val();
            console.log(code);
            $.ajax({
                type: "POST",
                url: "{{ url('handService') }}",
                data: { 
                    id: id,
                    code: code
                },
                dataType: 'json',
                success: function(id) {
                    $("#mostrarService-modal").modal('hide');
                    $('#services-table').DataTable().ajax.reload();
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
                        $('#codeError').html(error.responseJSON.errors.code);
                    } 
                }    
            });
        }
        function mostrarService(id){
            $("#mostrarService-modal").modal('show');
            $.ajax({
                type:'POST',
                url: "{{ url('mostrarService')}}",
                data: { id: id },
                cache:false,
                dataType: 'json',
                success: (data) => {
                    console.log(data);
                    $('#mostrarsolution').val(data.service.solution);
                    $('#mostrardescription').val(data.service.description);
                    if (data.service.status == 'TERMINADO') {
                        $('.mostrarapprove').html(
                            '<div class="row">'+
                                '<div class="col-md-3 col-sm-3 form-outline">'+
                                '</div>'+
                                '<div class="col-md-2 col-sm-2 form-outline">'+
                                    '<label class="form-label">{{__('Code')}}:</label>'+
                                '</div>'+
                                '<div class="col-md-2 col-sm-2 form-outline">'+
                                    '<input id="code" type="text" name="code" style="width: 100px !important;" class="form-control" onkeypress="return validaNumericos(event)">'+
                                '</div>'+
                                '<div class="col-md-2 col-sm-2 form-outline">'+
                                    '<a  style="margin-right: 6px !important;" onClick="handService('+data.service.id+')" title="Entregar" class="edit btn btn-primary " disabled>Entregar</a>'+
                                '</div>'+
                                '<div class="col-md-3 col-sm-3 form-outline">'+
                                '</div>'+
                                '<span id="codeError" class="text-danger error-messages"></span>'+
                            '</div>'
                        );
                    } else {
                        $('.mostrarapprove').html('');
                    }
                    $('#mostrardtmodal').html('');
                    $('.mostrardtmodal').html(   
                        '<table  id="payment" class="table table-striped">'+           
                            '<thead>'+
                                '<tr style="width:100%; text-align: center; font-size: 12px !important;" >'+
                                    '<th colspan="5" style=" text-align: center;">{{__('Service')}}</th>'+
                                '</tr>'+
                                '<tr style="width:100%; text-align: center; font-size: 12px !important;" >'+
                                    '<th style=" text-align: center;">{{__('Nº')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Name')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Quantity')}}</th>'+
                                    '<th style=" text-align: center;">{{__('Price')}} </th>'+
                                '</tr>'+
                            '</thead>'+
                            '<tbody class="mostrarproduct" style="font-size: 12px !important;">'+                          
                            '</tbody>'+
                        '</table>'
                    );  
                    var  i = 0;
                    $.each(data.serviceDetails, function(index, elemento){
                        i++;
                        $('.mostrarproduct').append(  
                            '<tr>'+
                                '<td style=" text-align: center;">'+i+'</td>'+
                                '<td>'+(elemento.procedure ?? elemento.product_name)+'</td>'+
                                '<td  style=" text-align: center;">'+elemento.quantity+'</td>'+
                                '<td style=" text-align: right;">'+elemento.price+'</td>'+
                            '</tr>'              
                        );  
                    }); 
                    $('.mostrartotales').html('');
                    $('.mostrarproduct').append(  
                        '<tr class="mostrartotales">'+
                            '<td style=" text-align: center;"></td>'+
                            '<td style=" text-align: center;"></td>'+
                            '<td style=" text-align: right;"><strong>Totales:</strong></td>'+
                            '<td style=" text-align: right;"><strong>'+data.service.price+'</strong></td>'+
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
        $('#single-select').change(function() {
            console.log('cambio');
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '/get-category-details/' + categoryId, // Route to fetch category details
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Hide all fields first
                        $('#brand-form-group').hide();
                        $('#model-form-group').hide();
                        $('#serial-form-group').hide();

                        if (data.brand == 1) {
                            $('#brand-form-group').show();
                        }
                        if (data.model == 1) {
                            $('#model-form-group').show();
                        }
                        if (data.serial == 1) {
                            $('#serial-form-group').show();
                        }
                    },
                    error: function(error) {
                        console.log('Error fetching category details:', error);
                        // Optionally, show an error message to the user
                    }
                });
            } else {
                // If no category is selected, hide the fields
                $('#brand-form-group').hide();
                $('#model-form-group').hide();
                $('#serial-form-group').hide();
            }
        });
    </script>
@endsection
