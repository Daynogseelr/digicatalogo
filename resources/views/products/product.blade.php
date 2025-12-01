@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'product'])
@section('content')
    <style>
        .dropzone {
            width: 30% !important;
            height: 80px !important;
            border: 2px dashed #ccc;
            text-align: center;
            padding: 5px;
            margin: 5px auto;
        }

        .imagendrop {
            width: 100% !important;
            height: 110px !important;
            text-align: center;
            padding: 5px;
            margin: 5px auto;
        }
    </style>
    <div class="container-fluid">
        <div class="row ">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12">
                                        <h4>{{ __('Products') }}</h4>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <label for="inventorySelect">Inventario:</label>
                                            <select id="inventorySelect" class="form-select">
                                                @foreach ($inventories as $inventory)
                                                    <option value="{{ $inventory->id }}">{{ $inventory->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="currencySelect">Moneda:</label>
                                            <select id="currencySelect" class="form-select">
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}" data-tasa="{{ $currency->rate }}"
                                                        {{ $currency->is_principal == 1 ? 'selected' : '' }}>
                                                        {{ $currency->name }} ({{ $currency->abbreviation }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="stockFilter">Filtro de Stock:</label>
                                            <select id="stockFilter" class="form-select">
                                                <option value="all">Todos</option>
                                                <option value="min">Mínimo stock</option>
                                                <option value="max">Máximo stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="font-size: 13px;">
                            {!! $dataTable->table(
                                ['class' => 'table table-striped table-bordered w-100', 'style' => 'font-size:13px;'],
                                true,
                            ) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap product model -->
    <div class="modal fade" id="product-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row" style="width:90%;">
                        <div class="col-6 col-sm-6 col-md-6 col-lg-8 col-xl-8">
                            <h5 class="modal-title" id="modal-title">{{ __('Add Product') }}</h5>
                        </div>
                        <div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2" id="inventario">

                        </div>
                        <div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2" id="status">

                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="productForm" name="productForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-3 col-sm-3 col-6 form-outline">
                                <input name="code" type="text" class="form-control" id="code"
                                    placeholder="{{ __('Code') }}" title="Es obligatorio un codigo" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }}</label>
                                <span id="codeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-6  form-outline">
                                <input name="code2" type="text" class="form-control" id="code2"
                                    placeholder="{{ __('Code') }} UPC" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} UPC</label>
                                <span id="code2Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-6  form-outline">
                                <input name="code3" type="text" class="form-control" id="code3"
                                    placeholder="{{ __('Code') }} EAN" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} EAN</label>
                                <span id="code3Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-6  form-outline">
                                <input name="code4" type="text" class="form-control" id="code4"
                                    placeholder="{{ __('Code') }} Alternativo" maxlength="50" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} Alternativo</label>
                                <span id="code4Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input class="form-control" id="description" name="description" onkeyup="mayus(this);"
                                    autocomplete="off" placeholder="{{ __('Description') }}"></input>
                                <label class="form-label" for="form2Example17">{{ __('Description') }}</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-10 col-sm-10 col-9 form-outline">
                                <select class="js-example-basic-multiple js-example-basic-multiple-category form-select"
                                    data-placeholder="Seleccione una categoría" name="id_category[]" multiple="multiple">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Category') }}</label>
                                <span id="id_categoryError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-3" style="margin-bottom:25px;">
                                <a class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center"
                                    onClick="addCategory()" href="javascript:void(0)">
                                    Agregar Categoria
                                </a>
                            </div>
                            <div class="col-md-2 col-sm-2 col-4 form-outline">
                                <input name="cost" type="text" class="form-control" id="cost"
                                    placeholder="{{ __('Cost') }}" title="Es obligatorio un precio" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Cost') }}</label>
                                <span id="costError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-4 form-outline">
                                <input name="utility" type="text" class="form-control" id="utility"
                                    placeholder="{{ __('Utility') }}" title="Es obligatorio un descuento" minlength="1"
                                    value="0" maxlength="10" required onkeypress='return validaMonto(event)'>
                                <label class="form-label" for="form2Example17">{{ __('Utility') }} %</label>
                                <span id="utilityError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-4 form-outline">
                                <input name="price" type="text" class="form-control" id="price"
                                    placeholder="{{ __('Price') }}" title="Es obligatorio un precio" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Price') }}</label>
                                <span id="priceError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-6 form-outline">
                                <select class="form-select" name="modal_currency_id" id="modal_currency_id">
                                    @foreach ($currencies as $currency)
                                        <option {{ $currency->is_principal == 1 ? 'selected' : '' }}
                                            value="{{ $currency->id }}">{{ $currency->abbreviation }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="modal_currency_id">Moneda</label>
                            </div>
                            <div class="col-md-3 col-sm-3 col-6 form-outline">
                                <input name="price2" type="text" class="form-control" id="price2"
                                    placeholder="{{ __('Price en moneda') }}" title="Es obligatorio un precio"
                                    minlength="1" maxlength="80" required autocomplete="off">
                                <label class="form-label" for="price2">{{ __('Price en moneda') }}</label>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="url[]" type="file" class="form-control" id="url" multiple
                                    title="Es obligatorio una Imagen">
                                <label class="form-label" for="form2Example17">{{ __('Image') }}</label>
                                <span id="urlError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-md-8 col-sm-8 form-outline">
                                <div class="row" id="divdropzone">
                                    <div class="col-md-4 col-sm-4 col-4" id="divimg1" data-url="url1">

                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4" id="divimg2" data-url="url2">

                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4" id="divimg3" data-url="url3">

                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4 text-center">
                                        {{-- Imagen se carga por JS --}}
                                        <button type="button" class="btn btn-danger btn-sm mt-2" id="delete-img1"
                                            style="display:none;" onclick="deleteImage(1)">
                                            Eliminar imagen
                                        </button>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4 text-center">
                                        <button type="button" class="btn btn-danger btn-sm mt-2" id="delete-img2"
                                            style="display:none;" onclick="deleteImage(2)">
                                            Eliminar imagen
                                        </button>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4 text-center">
                                        <button type="button" class="btn btn-danger btn-sm mt-2" id="delete-img3"
                                            style="display:none;" onclick="deleteImage(3)">
                                            Eliminar imagen
                                        </button>
                                    </div>
                                    <div class="dropzone col-md-4 col-sm-4 col-4" data-url="url1">
                                        {{ __('drop the image here') }}
                                    </div>
                                    <div class="dropzone col-md-4 col-sm-4 col-4" data-url="url2">
                                        {{ __('drop the image here') }}
                                    </div>
                                    <div class="dropzone col-md-4 col-sm-4 col-4" data-url="url3">
                                        {{ __('drop the image here') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-md-3 col-sm-3 col-4">
                                <input name="existencia" type="text" class="form-control" id="existencia"
                                    placeholder="{{ __('existencia') }}" minlength="1" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Existencia') }}</label>
                                <span id="ExistenciaError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-4">
                                <select class="form-select" name="stock" id="stock">
                                    <option value="1">{{ __('SI') }}</option>
                                    <option value="0">{{ __('NO') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Stock') }}</label>
                                <span id="stockError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-4">
                                <input type="text" class="form-control" id="stock_min" name="stock_min"
                                    onkeypress='return validaNumericos(event)' required>
                                <label class="form-label" for="form2Example17">{{ __('Stock minimo') }}</label>
                                <span id="stock_minError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-6">
                                <select class="form-select" name="serial" id="serial">
                                    <option value="0">{{ __('NO') }}</option>
                                    <option value="1">{{ __('SI') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Serial') }}</label>
                                <span id="serialError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 col-6">
                                <select class="form-select" name="type" id="type">
                                    <option value="NORMAL">{{ __('NORMAL') }}</option>
                                    <option value="INTEGRAL">{{ __('INTEGRAL') }}</option>
                                    <option value="FRACCIONADO">{{ __('FRACCIONADO') }}</option>
                                    <option value="SERVICIO">{{ __('SERVICIO') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Tipo') }}</label>
                                <span id="typeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12" id="selectProduct">
                                <select class="js-example-basic-multiple js-example-basic-multiple-product"
                                    name="id_product[]" multiple="multiple" data-placeholder="Seleccione un producto">
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->code }} {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Product') }}</label>
                                <span id="id_productError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12" id="divfraction">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3 form-outline">
                                        <input name="code_fraction" type="text" class="form-control"
                                            id="code_fraction" placeholder="{{ __('Codigo de Fracción') }}"
                                            title="Es obligatorio un codigo" minlength="1" maxlength="50"
                                            autocomplete="off">
                                        <label class="form-label"
                                            for="form2Example17">{{ __('Codigo de Fracción') }}</label>
                                        <span id="code_fractionError" class="text-danger error-messages"></span>
                                    </div>
                                    <div class="col-md-9 col-sm-9 form-outline">
                                        <input name="name_fraction" type="text" class="form-control"
                                            id="name_fraction" placeholder="{{ __('Nombre Fracción') }}"
                                            title="Es obligatorio un nombre" minlength="2" maxlength="200"
                                            onkeyup="mayus(this);" autocomplete="off">
                                        <label class="form-label"
                                            for="form2Example17">{{ __('Nombre Fracción') }}</label>
                                        <span id="name_fractionError" class="text-danger error-messages"></span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 form-outline">
                                        <input name="equivalence_fraction" type="text" class="form-control"
                                            id="equivalence_fraction" placeholder="{{ __('Equivalencia Fracción') }}"
                                            title="Es obligatorio un Equivalencia" minlength="1" maxlength="50"
                                            onkeypress='return validaMonto(event)' autocomplete="off">
                                        <label class="form-label"
                                            for="form2Example17">{{ __('Equivalencia Fracción') }}</label>
                                        <span id="equivalence_fractionError" class="text-danger error-messages"></span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 form-outline">
                                        <input name="price_fraction" type="text" class="form-control"
                                            id="price_fraction" placeholder="{{ __('Precio Fracción') }}"
                                            title="Es obligatorio un precio" minlength="1" maxlength="50"
                                            onkeypress='return validaMonto(event)' autocomplete="off">
                                        <label class="form-label"
                                            for="form2Example17">{{ __('Precio Fracción') }}</label>
                                        <span id="price_fractionError" class="text-danger error-messages"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12" id="productQuantity" class="table-responsive"
                                style="max-height: 300px; overflow-y: auto;">
                            </div>
                            <div id="service-integral-table-container"></div>
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
    <!-- boostrap product Stock model -->
    <div class="modal fade" id="productStock-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStock-title">{{ __('Replenish Stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="productStockForm" name="productStockForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_product" id="id_product">
                        <input type="hidden" name="status" id="status">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="codeStock" type="text" class="form-control" id="codeStock"
                                    placeholder="{{ __('Code') }}" title="Es obligatorio un codigo" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off" disabled>
                                <label class="form-label" for="form2Example17">{{ __('Code') }}</label>
                                <span id="codeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="nameStock" type="text" class="form-control" id="nameStock"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off" disabled>
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <textarea class="form-control" rows="3" id="descriptions" name="descriptions" onkeyup="mayus(this);"
                                    autocomplete="off"></textarea>
                                <label class="form-label" for="form2Example17">{{ __('Description') }}</label>
                                <span id="descriptionsError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="stocks" type="text" class="form-control" id="stocks"
                                    placeholder="{{ __('Stock') }}" title="Es obligatorio un stocks" minlength="1"
                                    maxlength="10" required onkeypress='return validaMonto(event)' onkeyup="mayus(this);"
                                    autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Stock') }}</label>
                                <span id="stockError" class="text-danger error-messages"></span>
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
    <!-- boostrap category model -->
    <div class="modal fade" id="category-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="categoryForm" name="categoryForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_category" id="id_category">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="30" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="description" type="text" class="form-control" id="description"
                                    placeholder="{{ __('Description') }}" title="Es obligatorio una descripcion"
                                    minlength="2" maxlength="100" required onkeyup="mayus(this);" autocomplete="off">
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
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                dropdownParent: $('#product-modal .modal-body'),
                language: "es"
            });
            $('.js-example-basic-multiple-product').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#product-modal .modal-body'),
                language: "es"
            });
            $('.js-example-basic-multiple-category').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#product-modal .modal-body'),
                language: "es"
            });
            var table = $('#products-table').DataTable();
            $('#selectProduct').hide();
            $('#divfraction').hide();

            function reloadTable() {
                table.ajax.reload();
            }
            $('#inventorySelect, #currencySelect, #stockFilter').on('change', reloadTable);

            // Sobrescribe la función ajax para enviar los parámetros
            $.fn.dataTable.ext.errMode = 'none'; // Evita errores JS en consola
            $('#products-table').DataTable().on('preXhr.dt', function(e, settings, data) {
                data.inventory_id = $('#inventorySelect').val();
                data.currency_id = $('#currencySelect').val();
                data.stock_filter = $('#stockFilter').val();
            });
        });

        const currencies = @json($currencies);
        const mainCurrencyId = {{ $currencyPrincipal->id }};
        let tasaCambio = 1;
        var productDataTable; // Declare DataTable variable outside for wider scope

        const products = @json($products);
        window.modosFraccion = window.modosFraccion || {};
        $(document).on('change', '.js-example-basic-multiple-product', function(event) {
            let selectedType = $('select[name=type]').val();
            let selectedProductIds = $(this).val();
            // Guarda cantidades previas ANTES de vaciar el contenedor
            let cantidadesPrevias = {};
            $('#service-integral-table-container tbody tr').each(function() {
                let pid = $(this).find('.service-integral-qty').data('id');
                let cant = $(this).find('.service-integral-qty').val();
                if (pid) cantidadesPrevias[pid] = cant;
            });

            let $container = $('#service-integral-table-container');
            $container.empty();

            if (!selectedProductIds || selectedProductIds.length === 0) return;

            let currencyId = $('#modal_currency_id').val();
            let tasaCambio = 1;
            currencies.forEach(currency => {
                if (currency.id == currencyId) tasaCambio = currency.rate2;
            });

            let table = `<table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Cantidad</th>
                                    <th>${selectedType === 'SERVICIO' ? 'Precio Unitario' : 'Costo Unitario'}</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>`;
            window.modosFraccion = window.modosFraccion || {};
            let totalGeneral = 0;
            selectedProductIds.forEach(productId => {
                const product = products.find(p => p.id == productId);
                if (product) {
                    let cantidad = 1;
                    if (typeof cantidadesPrevias[productId] !== 'undefined') {
                        cantidad = cantidadesPrevias[productId];
                    } else if (window.productData && window.productData.quantities && typeof window
                        .productData.quantities[productId] !== 'undefined') {
                        cantidad = window.productData.quantities[productId];
                    }
                    let modo = 'completo';
                    let unitario = 0;
                    let nombreMostrar = product.name;

                    // Selector de modo solo si el producto es FRACCIONADO
                    let modoSelector = '';
                    if (product.type === 'FRACCIONADO') {
                        if (window.modosFraccion && window.modosFraccion[productId]) {
                            modo = window.modosFraccion[productId];
                        }
                        modoSelector = `
                                        <select class="form-select modo-fraccion" data-id="${product.id}" style="width:120px;display:inline-block;">
                                            <option value="completo" ${modo === 'completo' ? 'selected' : ''}>Completo</option>
                                            <option value="fraccion" ${modo === 'fraccion' ? 'selected' : ''}>Fracción</option>
                                        </select>
                                    `;
                        // Si está en modo fracción, cambia el nombre y calcula el precio/costo
                        if (modo === 'fraccion') {
                            nombreMostrar = product.name_fraction ? product.name_fraction :
                                product.name;
                            if (selectedType === 'SERVICIO') {
                                unitario = product.price_fraction ? parseFloat(product
                                    .price_fraction) * tasaCambio : 0;
                            } else if (selectedType === 'INTEGRAL') {
                                unitario = (product.cost && product.equivalence_fraction) ? (
                                    parseFloat(product.cost) / parseFloat(product
                                        .equivalence_fraction)) : 0;
                            } else {
                                unitario = product.price_fraction ? parseFloat(product
                                    .price_fraction) * tasaCambio : 0;
                            }
                        } else {
                            // Si está en modo completo, calcula según el tipo seleccionado
                            if (selectedType === 'SERVICIO') {
                                unitario = product.price ? parseFloat(product.price) *
                                    tasaCambio : 0;
                            } else if (selectedType === 'INTEGRAL') {
                                unitario = product.cost ? parseFloat(product.cost) : 0;
                            } else {
                                unitario = product.price ? parseFloat(product.price) *
                                    tasaCambio : 0;
                            }
                        }
                    } else {
                        // Para productos NO fraccionados, calcula según el tipo seleccionado
                        if (selectedType === 'SERVICIO') {
                            unitario = product.price ? parseFloat(product.price) * tasaCambio :
                                0;
                        } else if (selectedType === 'INTEGRAL') {
                            unitario = product.cost ? parseFloat(product.cost) : 0;
                        } else {
                            unitario = product.price ? parseFloat(product.price) * tasaCambio :
                                0;
                        }
                    }

                    let total = unitario * cantidad;
                    totalGeneral += total;

                    table += `<tr>
                            <td>${product.code}</td>
                            <td>${nombreMostrar} ${modoSelector}</td>
                            <td><input type="number" name="quantity[${product.id}]" class="form-control service-integral-qty" data-id="${product.id}" value="${cantidad}" min="1" style="width:80px"></td>
                            <td class="unitario" data-id="${product.id}">${unitario.toFixed(2)}</td>
                            <td class="total" data-id="${product.id}">${total.toFixed(2)}</td>
                        </tr>`;
                }
            });

            table += `</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align:right"><b>Total ${selectedType === 'SERVICIO' ? 'Servicios' : 'Costos'}:</b></td>
                                    <td id="service-integral-total"><b>${totalGeneral.toFixed(2)}</b></td>
                                </tr>
                            </tfoot>
                        </table>`;

            $container.html(table);

            if (selectedType === 'SERVICIO') {
                let price = parseFloat($('#price').val()) || 0;
                let priceTotal = totalGeneral + (price * tasaCambio);
                $('#price2').val(priceTotal.toFixed(2));
            } else {
                $('#cost').val(totalGeneral.toFixed(2));
                $('#cost').trigger('input');
            }
        });
        $(document).on('change', '.modo-fraccion', function() {
            let productId = $(this).data('id');
            let modo = $(this).val();
            window.modosFraccion[productId] = modo;
            // Vuelve a disparar el evento de cambio para reconstruir la tabla con el nuevo modo
            $('.js-example-basic-multiple-product').trigger('change');
        });

        // Evento input para los campos del modal
        $('#product-modal').on('input', '#cost, #utility, #price, #price2, #modal_currency_id', function() {
            const costInput = $('#cost');
            const utilidadInput = $('#utility');
            const precioInput = $('#price');
            const precio2Input = $('#price2');
            const currencyId = $('#modal_currency_id').val();
            let selectedType = $('#type').val();
            // Tasa de cambio según la moneda seleccionada en el modal
            let tasaCambio = 1;
            currencies.forEach(currency => {
                if (currency.id == currencyId) {
                    tasaCambio = currency.rate2;
                }
            });
            let costo = parseFloat(costInput.val()) || 0;
            let utilidad = parseFloat(utilidadInput.val()) || 0;
            let precio = parseFloat(precioInput.val()) || 0;
            let precio2 = parseFloat(precio2Input.val()) || 0;

            if ($(this).attr('id') === 'cost' || $(this).attr('id') === 'utility' || $(this).attr(
                    'id') === 'modal_currency_id') {
                if (utilidad > 99.99) {
                    utilidad = 99.99;
                    utilidadInput.val(99.99);
                }
                if (utilidad < 0) {
                    utilidad = 0;
                    utilidadInput.val(0.00);
                }
                precio = costo / (1 - utilidad / 100);
                precioInput.val(precio.toFixed(2));
                precio2 = precio * tasaCambio;
                precio2Input.val(precio2.toFixed(2));
            } else if ($(this).attr('id') === 'price') {
                if (costo !== 0) {
                    utilidad = 100 - ((costo * 100) / precio);
                    if (utilidad > 99.99) {
                        utilidad = 99.99;
                        utilidadInput.val(99.99);
                    } else if (utilidad < 0) {
                        utilidad = 0;
                        utilidadInput.val(0.00);
                    } else {
                        utilidadInput.val(utilidad.toFixed(2));
                    }
                } else {
                    utilidadInput.val(0.00);
                }
                precio2 = precio * tasaCambio;
                precio2Input.val(precio2.toFixed(2));
            } else if ($(this).attr('id') === 'price2') {
                if (tasaCambio !== 0) {
                    precio = precio2 / tasaCambio;
                    precioInput.val(precio.toFixed(2));
                    if (costo !== 0) {
                        utilidad = 100 - ((costo * 100) / precio);
                        if (utilidad > 99.99) {
                            utilidad = 99.99;
                            utilidadInput.val(99.99);
                        } else if (utilidad < 0) {
                            utilidad = 0;
                            utilidadInput.val(0.00);
                        } else {
                            utilidadInput.val(utilidad.toFixed(2));
                        }
                    } else {
                        utilidadInput.val(0.00);
                    }
                } else {
                    precioInput.val(0.00);
                }
            }

            // SOLO PARA SERVICIO: suma el total de la tabla de servicios + el precio individual convertido
            if (selectedType === 'SERVICIO') {
                let totalGeneral = parseFloat($('#service-integral-total').text()) || 0;
                let price = parseFloat($('#price').val()) || 0;
                let priceTotal = totalGeneral + (price * tasaCambio);
                $('#price2').val(priceTotal.toFixed(2));
            }
        });
        // Actualiza el precio2 en el modal según la moneda seleccionada
        $('#modal_currency_id').on('change', function() {
            let selectedType = $('select[name=type]').val();
            if (selectedType !== 'SERVICIO') return;

            let currencyId = $(this).val();
            let tasaCambio = 1;
            currencies.forEach(currency => {
                if (currency.id == currencyId) tasaCambio = currency.rate2;
            });

            let totalGeneral = 0;
            $('#service-integral-table-container tbody tr').each(function() {
                let productId = $(this).find('.service-integral-qty').data('id');
                let cantidad = parseFloat($(this).find('.service-integral-qty').val()) || 1;
                const product = products.find(p => p.id == productId);
                let unitario = selectedType === 'SERVICIO' ?
                    (parseFloat(product.price) * tasaCambio) :
                    (parseFloat(product.cost));
                let total = unitario * cantidad;
                $(this).find('.unitario').text(unitario.toFixed(2));
                $(this).find('.total').text(total.toFixed(2));
                totalGeneral += total;
            });

            $('#service-integral-total').text(totalGeneral.toFixed(2));
            if (selectedType === 'SERVICIO') {
                // Suma de todos los servicios + el precio individual convertido
                let price = parseFloat($('#price').val()) || 0;
                let priceTotal = totalGeneral + (price * tasaCambio);
                $('#price2').val(priceTotal.toFixed(2));
            } else {
                $('#cost').val(totalGeneral.toFixed(2));
                $('#cost').trigger('input');
            }
        });
        let productData;

        function editFunc(id) {
            $('#productForm').trigger("reset");
            $('#description').html('');
            var id_inventory = $('#inventorySelect').val();
            $.ajax({
                type: "POST",
                url: "{{ url('editProduct') }}",
                data: {
                    id_inventory: id_inventory,
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    productData = res;
                    window.productData = res;
                    $('#modal-title').html("{{ __('Editar Producto') }}");
                    $('.error-messages').html('');
                    let productId = res.product.id;
                    let productStatus = res.product.status;
                    let inventarioHref = '/indexStock/' +
                        productId; // Asumiendo que la ruta base es /indexStock

                    // Construcción del enlace de Inventario usando la variable href
                    let inventarioLink = '<a style="color:white;" href="' + inventarioHref +
                        '" data-toggle="tooltip" ' +
                        'data-original-title="Inventario" class="btn btn-info">' +
                        'Kardex' +
                        '</a>';

                    $('#inventario').html(inventarioLink);
                    $('#status').html(function() {
                        let html = '';
                        if (productStatus == '1') {
                            html = '<a class="btn btn-success cambia' + productId +
                                '" href="javascript:void(0)" onClick="micheckbox(' +
                                productId + ')">' +
                                'Activo' +
                                '</a>';
                        } else {
                            html = '<a class="btn btn-danger cambia' + productId +
                                '" href="javascript:void(0)" onClick="micheckbox(' +
                                productId + ')">' +
                                'Inactivo' +
                                '</a>';
                        }
                        return html;
                    });
                    $('#product-modal').modal('show');
                    $('#divdropzone').show();
                    $('#id').val(res.product.id);
                    $('#code').val(res.product.code);
                    $('#code2').val(res.product.code2);
                    $('#code3').val(res.product.code3);
                    $('#code4').val(res.product.code4);
                    $('#name').val(res.product.name);
                    $('#description').val(res.product.description);
                    $('#cost').val(res.product.cost);
                    $('#utility').val(res.product.utility);
                    $('#price').val(res.product.price);
                    $('#price2').val(res.product.price);
                    $('#existencia').val(res.quantity ? res.quantity.quantity : 0);
                    $('#stock').val(res.product.stock);
                    $('#stock_min').val(res.product.stock_min);
                    $('#serial').val(res.product.serial);
                    console.log(res.product.type);
                    $('#type').val(res.product.type);
                    $('#divfraction').hide();
                    $('.js-example-basic-multiple-product').val(null).trigger('change');
                    $('#productQuantity').html('');
                    for (var i = 1; i <= 3; i++) {
                        var imageUrl = res.product['url' + i];
                        if (imageUrl) {
                            // Añade un cache-buster para evitar cacheo del navegador y forzar recarga
                            var fullImageUrl = '{{ asset('storage') }}' + '/' + imageUrl + '?t=' + Date.now();
                            $('#divimg' + i).html('<img class="imagendrop" src="' + fullImageUrl +
                                '" onerror="this.onerror=null;this.src=\'{{ asset('storage/products/product.png') }}?t=' + Date.now() + '\'" alt="Product Image ' +
                                i + '">');
                            $('#delete-img' + i).show();
                        } else {
                            $('#divimg' + i).html(
                                '<img class="imagendrop" src="{{ asset('storage/products/product.png') }}?t=' + Date.now() + '">' 
                            );
                            $('#delete-img' + i).hide();
                        }
                    }
                    if (res.product.type == 'FRACCIONADO') {
                        $('#divfraction').show();
                        $('#code_fraction').attr('required', true);
                        $('#name_fraction').attr('required', false);
                        $('#equivalence_fraction').attr('required', false);
                        $('#price_fraction').attr('required', false);

                        $('#code_fraction').val(res.product.code_fraction);
                        $('#name_fraction').val(res.product.name_fraction);
                        $('#equivalence_fraction').val(res.product.equivalence_fraction);
                        $('#price_fraction').val(res.product.price_fraction);
                    } else {
                        $('#code_fraction').attr('required', false);
                        $('#name_fraction').attr('required', false);
                        $('#equivalence_fraction').attr('required', false);
                        $('#price_fraction').attr('required', false);

                        $('#code_fraction').val('');
                        $('#name_fraction').val('');
                        $('#equivalence_fraction').val('');
                        $('#price_fraction').val('');
                    }
                    productQuantity
                    if (res.product.type == 'SERVICIO' || res.product.type == 'INTEGRAL') {
                        $('#selectProduct').show();
                        if (res.productI && res.productI.length > 0) {
                            var selectedIds = res.productI.map(function(item) {
                                return item.id_product;
                            });
                            $('.js-example-basic-multiple-product').val(selectedIds).trigger('change');

                            // Espera a que la tabla se renderice y luego asigna cantidades y modos fraccionados
                            setTimeout(function() {
                                // Asigna modos fraccionados primero
                                if (res.modosFraccion) {
                                    Object.keys(res.modosFraccion).forEach(function(pid) {
                                        $(`select.modo-fraccion[data-id="${pid}"]`).val(res.modosFraccion[pid]);
                                        window.modosFraccion[pid] = res.modosFraccion[pid]; // <-- Esto es lo importante
                                    });
                                }
                                // Asigna cantidades después de los modos
                                if (res.quantities) {
                                    Object.keys(res.quantities).forEach(function(pid) {
                                        $(`input.service-integral-qty[data-id="${pid}"]`).val(res.quantities[pid]);
                                    });
                                }
                                // Reconstruye la tabla para que el nombre se actualice acorde al modo
                                $('.js-example-basic-multiple-product').trigger('change');
                                window.productData = null;
                            }, 300);
                        }
                    } else {
                        $('#selectProduct').hide();
                    }
                    if (res.categories && res.categories.length > 0) {
                        var selectedIds = res.categories.map(function(item) {
                            return item.id_category;
                        });
                        $('.js-example-basic-multiple-category').val(selectedIds).trigger('change');
                    } else {
                        $('.js-example-basic-multiple-category').val('').trigger('change');
                    }
                }
            });
        }

        function deleteImage(index) {
            var productId = $('#id').val();
            var urlField = 'url' + index;
            Swal.fire({
                title: "¿Seguro que deseas eliminar la imagen?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('deleteProductImage') }}",
                        type: "POST",
                        data: {
                            id: productId,
                            url: urlField,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: "Imagen eliminada",
                                showConfirmButton: false,
                                timer: 1200
                            });
                            // Recarga la imagen y oculta el botón
                            $('#divimg' + index).html(
                                '<img class="imagendrop" src="{{ asset('storage/products/product.png') }}">'
                            );
                            $('#delete-img' + index).hide();
                            $('#products-table').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire("Error", "No se pudo eliminar la imagen", "error");
                        }
                    });
                }
            });
        }

        function editStock(id, stock) {
            $.ajax({
                type: "POST",
                url: "{{ url('editProduct') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    if (stock == 'Reponer') {
                        $('#modalStock-title').html("{{ __('Replenish Stock') }}");
                        $('#status').val('Reponer');
                    } else {
                        $('#modalStock-title').html("{{ __('Subtract Stock') }}");
                        $('#status').val('Restar');
                    }
                    $('.error-messages').html('');
                    $('#productStock-modal').modal('show');
                    $('#id_product').val(res.product.id);
                    $('#codeStock').val(res.product.code);
                    $('#nameStock').val(res.product.name);
                    $('#stocks').val('');
                }
            });
        }
        $('#productStockForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            // Antes de enviar, agrega los valores de is_fraction por producto fraccionado
            // Busca todos los selects de modo fraccion y agrega un input oculto por cada uno
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeStock') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#productStock-modal").modal('hide');
                    $('#products-table').DataTable().ajax.reload();
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
                        $('#stocksError').html(error.responseJSON.errors.stocks);
                        $('#descriptionsError').html(error.responseJSON.errors
                            .descriptions);
                    }
                }
            });
        });

        function deleteFuncAp(id) {
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

        function deleteFunc(id) {
            var id = id;
            // ajax
            $.ajax({
                type: "POST",
                url: "{{ url('deleteProduct') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
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
                    title: "{{ __('Image') }}?",
                    text: "{{ __('You cannot upload more than three images') }}",
                    icon: "question"
                });
                $('#url').val('')
            } else {
                $('.modo-fraccion').each(function() {
                    let productId = $(this).data('id');
                    let modo = $(this).val();
                    let is_fraction = (modo === 'fraccion') ? 1 : 0;
                    // Elimina input oculto previo si existe
                    $('#productForm').find('input[name="is_fraction[' + productId + ']"]')
                        .remove();
                    // Agrega input oculto
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'is_fraction[' + productId + ']')
                        .val(is_fraction)
                        .appendTo('#productForm');
                });
                var id_inventory = $('#inventorySelect').val();
                $('.error-messages').html('');
                var formData = new FormData(this);
                formData.append('id_inventory', id_inventory);
                $.ajax({
                    type: 'POST',
                    url: "{{ url('storeProduct') }}",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#product-modal").modal('hide');
                        $('#products-table').DataTable().ajax.reload();
                        $("#btn-save").html('Enviar');
                        $("#btn-save").attr("disabled", false);
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
                            $('#code2Error').html(error.responseJSON.errors.code2);
                            $('#code3Error').html(error.responseJSON.errors.code3);
                            $('#code4Error').html(error.responseJSON.errors.code4);
                            $('#nameError').html(error.responseJSON.errors.name);
                            $('#priceError').html(error.responseJSON.errors.price);
                            $('#stockError').html(error.responseJSON.errors.stock);
                            $('#serialError').html(error.responseJSON.errors.serial);
                            $('#descriptionError').html(error.responseJSON.errors
                                .description);
                            $('#urlError').html(error.responseJSON.errors.url);
                        }
                    }
                });
            }
        });

        function micheckbox(id) {
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id;
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusProduct') }}",
                data: {
                    'id': id
                },
                success: function(data) {
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Modified status') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#status').html(function() {
                        let html = '';
                        if (data.status == '1') {
                            html = '<a class="btn btn-success cambia' + id +
                                '" href="javascript:void(0)" onClick="micheckbox(' + id +
                                ')">' +
                                'Activo' +
                                '</a>';
                        } else {
                            html = '<a class="btn btn-danger cambia' + id +
                                '" href="javascript:void(0)" onClick="micheckbox(' + id +
                                ')">' +
                                'Inactivo' +
                                '</a>';
                        }
                        return html;
                    });
                }
            });
        }

        function dropHandler(ev) {
            console.log(ev.target.dataset);
            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
            // Check for dropped items
            if (ev.dataTransfer.items) {
                [...ev.dataTransfer.items].forEach((item, i) => {
                    // Only process files
                    if (item.kind === "file") {
                        const file = item.getAsFile();
                        console.log(`… file[${i}].name = ${file.name}`);
                        // Check if the file is an image
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                const base64Image = event.target.result.split(',')[
                                    1]; // Extract base64 data
                                const rawUrl = ev.target.dataset.url;
                                // Normalize data-url to expected backend field: url1, url2 or url3
                                let url = null;
                                if (rawUrl) {
                                    // Match patterns like 'url1', 'url-1', '1', etc.
                                    let m = rawUrl.match(/url[-_\s]*(\d+)/i) || rawUrl.match(/(\d+)/);
                                    if (m && m[1]) {
                                        url = 'url' + m[1];
                                    } else if (rawUrl.toLowerCase() === 'url') {
                                        url = 'url1';
                                    } else {
                                        url = rawUrl; // keep as provided as fallback
                                    }
                                }
                                const id = $('#id').val();
                                const formData = new FormData();
                                formData.append('image', base64Image);
                                formData.append('url', url);
                                formData.append('id', id);
                                // Ensure CSRF token is included and debug the payload
                                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                                // Validate url field expected by backend
                                if (!url || !['url1','url2','url3'].includes(url)) {
                                    console.warn('Invalid url field for upload-image:', url);
                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Error: campo url inválido',
                                        text: 'El campo url debe ser url1, url2 o url3',
                                        showConfirmButton: true
                                    });
                                    return;
                                }
                                console.log('Uploading image to /upload-image', { id: id, url: url, base64Length: base64Image.length });
                                // Send image data to Laravel controller via AJAX
                                $.ajax({
                                    url: "{{ url('/upload-image') }}",
                                    method: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "success",
                                            title: "{{ __('Updated Image') }}",
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#products-table').DataTable().ajax.reload();
                                        editFunc(response);
                                    },
                                    error: function(xhr) {
                                        console.error('upload-image error', xhr.status, xhr.responseText, xhr.responseJSON);
                                        // Build readable message from validation errors if present
                                        var msg = 'Error al subir la imagen';
                                        if (xhr.responseJSON) {
                                            if (xhr.responseJSON.errors) {
                                                // Join all validation messages
                                                msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                                            } else if (xhr.responseJSON.message) {
                                                msg = xhr.responseJSON.message;
                                            } else {
                                                msg = JSON.stringify(xhr.responseJSON);
                                            }
                                        } else if (xhr.responseText) {
                                            msg = xhr.responseText;
                                        }
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "error",
                                            title: "Error",
                                            text: msg,
                                            showConfirmButton: true
                                        });
                                    }
                                });
                            };
                            reader.readAsDataURL(file); // Read image as base64
                        } else {
                            Swal.fire({
                                position: "top-end",
                                icon: "error",
                                title: "((__('Something is wrong')))",
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            } else {
                console.error('No files dropped.');
            }
        }
        // Event listener for dropzone
        var dropzones = document.querySelectorAll('[data-url]');
        dropzones.forEach(dropzone => {
            dropzone.addEventListener('drop', dropHandler);
            dropzone.addEventListener('dragover', (ev) => {
                ev.preventDefault();
            });
        });


        function addCategory() {
            $('#categoryForm').trigger("reset");
            $('.error-messages').html('');
            $('#category-modal').modal('show');
            $('#id_category').val('');
        }
        $('#categoryForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeCategory') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#category-modal").modal('hide');
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    var newOption = new Option(data.name, data.id, false, true);
                    // Obtener el select y agregar la nueva opción
                    var selectCategory = $('.js-example-basic-multiple-category');
                    selectCategory.append(newOption).trigger(
                        'change'); // Append y trigger 'change' para Select2
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#descriptionError').html(error.responseJSON.errors.description);
                    }
                }
            });
        });
        $(document).on('change', 'select[name=type]', function(event) {
            let selectedValue = $(this).val();
            $('.js-example-basic-multiple-product').val(null).trigger('change');
            $('#selectProduct').hide();
            $('#divfraction').hide();
            $('#name_fraction').val('');
            $('#price_fraction').val('');
            $('#equivalence_fraction').val('');
            $('#code_fraction').val('');
            if (selectedValue == 'SERVICIO' || selectedValue == 'INTEGRAL') {
                $('#selectProduct').show();
                $('#service-integral-table-container').remove(); // Limpia tabla anterior si existe
                $('#selectProduct').after('<div id="service-integral-table-container"></div>');
            } else if (selectedValue == 'FRACCIONADO') {
                $('#divfraction').show();
                $('#equivalence_fraction').val(1);
                let price = parseFloat($('#price').val());
                if (isNaN(price) || price <= 0) {
                    $('#price').val(1);
                    price = 1;
                }
                $('#price_fraction').val((price / 1).toFixed(2));
            } else {
                $('#service-integral-table-container').remove();
            }
        });
        $('#equivalence_fraction, #price').on('input', function() {
            let price = parseFloat($('#price').val());
            let eq = parseFloat($('#equivalence_fraction').val());
            if (isNaN(price) || price <= 0) price = 1;
            if (isNaN(eq) || eq <= 0) eq = 1;
            $('#price_fraction').val((price / eq).toFixed(2));
        });
        // Cuando cambia la cantidad en la tabla
        $(document).on('input', '.service-integral-qty', function() {
            let selectedType = $('select[name=type]').val();
            let currencyId = $('#modal_currency_id').val();
            let tasaCambio = 1;
            currencies.forEach(currency => {
                if (currency.id == currencyId) tasaCambio = currency.rate2;
            });

            let totalGeneral = 0;
            $('#service-integral-table-container tbody tr').each(function() {
                let productId = $(this).find('.service-integral-qty').data('id');
                let cantidad = parseFloat($(this).find('.service-integral-qty').val()) || 1;
                const product = products.find(p => p.id == productId);

                let unitario = 0;

                // Si el producto es fraccionado, revisa el modo
                if (product.type === 'FRACCIONADO') {
                    let modo = $(this).find('.modo-fraccion').val() || 'completo';
                    if (modo === 'fraccion') {
                        if (selectedType === 'SERVICIO') {
                            unitario = product.price_fraction ? parseFloat(product.price_fraction) *
                                tasaCambio : 0;
                        } else if (selectedType === 'INTEGRAL') {
                            unitario = (product.cost && product.equivalence_fraction) ? (parseFloat(product
                                .cost) / parseFloat(product.equivalence_fraction)) : 0;
                        } else {
                            unitario = product.price_fraction ? parseFloat(product.price_fraction) *
                                tasaCambio : 0;
                        }
                    } else {
                        // Modo completo
                        if (selectedType === 'SERVICIO') {
                            unitario = product.price ? parseFloat(product.price) * tasaCambio : 0;
                        } else if (selectedType === 'INTEGRAL') {
                            unitario = product.cost ? parseFloat(product.cost) : 0;
                        } else {
                            unitario = product.price ? parseFloat(product.price) * tasaCambio : 0;
                        }
                    }
                } else {
                    // Producto normal
                    if (selectedType === 'SERVICIO') {
                        unitario = product.price ? parseFloat(product.price) * tasaCambio : 0;
                    } else if (selectedType === 'INTEGRAL') {
                        unitario = product.cost ? parseFloat(product.cost) : 0;
                    } else {
                        unitario = product.price ? parseFloat(product.price) * tasaCambio : 0;
                    }
                }

                let total = unitario * cantidad;
                $(this).find('.unitario').text(unitario.toFixed(2));
                $(this).find('.total').text(total.toFixed(2));
                totalGeneral += total;
            });

            $('#service-integral-total').text(totalGeneral.toFixed(2));
            if (selectedType === 'SERVICIO') {
                let price = parseFloat($('#price').val()) || 0;
                let priceTotal = totalGeneral + (price * tasaCambio);
                $('#price2').val(priceTotal.toFixed(2));
            } else {
                $('#cost').val(totalGeneral.toFixed(2));
                $('#cost').trigger('input');
            }
        });
    </script>
@endsection
