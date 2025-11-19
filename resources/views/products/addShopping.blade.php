@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'shopping'])
@section('content')
    <style>
        .product td input {
            width: 100%;
            text-align: right;
            margin: 0 auto;
            display: block;
        }
    </style>
    <div class="container-fluid py-1">
        <div class="row mt-1">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info mb-2" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-7 col-sm-8">
                                        <h4>{{ __('Add Shopping') }}</h4>
                                    </div>
                                    <div class="col-5 col-sm-4 text-end">
                                        <a class="btn btn-danger2" onClick="add()" href="javascript:void(0)">
                                            Agregar nuevo producto <i class="fa-solid fa-circle-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form id="shoppingForm" name="shoppingForm" class="form-horizontal" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-1 col-sm-1 form-outline">
                                    <select class="form-select" name="id_inventory" id="id_inventory">
                                        @foreach ($inventories as $inventory)
                                            <option value="{{ $inventory->id }}">{{ $inventory->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label" for="form2Example17">{{ __('Inventory') }}</label>
                                    <span id="id_inventoryError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="codeBill" type="text" class="form-control" id="codeBill"
                                        placeholder="{{ __('Code') }} {{ __('Bill') }}"
                                        title="Es obligatorio un codigo" minlength="1" maxlength="100" required
                                        onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Code') }}
                                        {{ __('Bill') }}</label>
                                    <span id="codeBillError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="date" type="date" class="form-control" id="date" required
                                        onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Date') }}</label>
                                    <span id="dateError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-5 col-sm-5 form-outline">
                                    <input name="name" type="text" class="form-control" id="name"
                                        placeholder="{{ __('Name') }} del Proveedor" title="Es obligatorio un nombre"
                                        minlength="2" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Name') }} del
                                        Proveedor</label>
                                    <span id="nameError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="total" type="text" class="form-control" id="total"
                                        placeholder="{{ __('Total') }} {{ __('Bill') }}"
                                        title="Es obligatorio un precio" minlength="1" maxlength="50" required
                                        onkeypress='return validaMonto(event)' autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Total') }}
                                        {{ __('Bill') }}</label>
                                    <span id="totalError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <select class="js-example-basic-multiple js-example-basic-multiple-products"
                                        name="id_product[]" multiple="multiple" data-placeholder="Seleccione un producto">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->code }}
                                                {{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label" for="form2Example17">{{ __('Product') }}</label>
                                    <span id="id_productError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <div class="tabla table-responsive">
                                        <br>
                                        <table id="payment" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="9" style="text-align: center;">{{ __('Products') }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Nº') }}</th>
                                                    <th>{{ __('Code') }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Quantity') }}</th>
                                                    <th>{{ __('Cost') }}</th>
                                                    <th>{{ __('Utility') }} %</th>
                                                    <th>{{ __('Price') }}</th>
                                                    <th>
                                                        <select class="form-select form-select-sm" id="currency_table_id">
                                                            @foreach ($currencies as $currency)
                                                                <option
                                                                    {{ $currency->is_principal == 1 ? 'selected' : '' }}
                                                                    value="{{ $currency->id }}">
                                                                    {{ $currency->abbreviation }}</option>
                                                            @endforeach
                                                        </select>
                                                    </th>
                                                    <th>{{ __('Seriales') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="product" style="font-size: 12px !important;">
                                                <tr class="no-products-message">
                                                    <td style="text-align: center;" colspan="9">No hay productos
                                                        seleccionados.</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr style="text-align: center;">
                                                    <td colspan="4" style="text-align: right;">
                                                        <strong>Totales:</strong>
                                                    </td>
                                                    <td id="totalCost">0.00</td>
                                                    <td id="totalUtility">0.00</td>
                                                    <td id="totalPrice">0.00</td>
                                                    <td id="totalPrice2">0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-sm-offset-2 col-sm-12 text-center btn-solution">
                                    <button type="submit" class="btn btn-primary"
                                        id="btn-send">{{ __('Send') }}</button>
                                </div>
                        </form>
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
                    <h5 class="modal-title" id="modal-title">{{ __('Add Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm" name="productForm" class="form-horizontal" method="POST"
                        enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-3 col-sm-3 form-outline">
                                <input name="code" type="text" class="form-control" id="code"
                                    placeholder="{{ __('Code') }}" title="Es obligatorio un codigo" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }}</label>
                                <span id="codeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
                                <input name="code2" type="text" class="form-control" id="code2"
                                    placeholder="{{ __('Code') }} UPC" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} UPC</label>
                                <span id="code2Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
                                <input name="code3" type="text" class="form-control" id="code3"
                                    placeholder="{{ __('Code') }} EAN" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} EAN</label>
                                <span id="code3Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
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
                            <div class="col-md-10 col-sm-10 form-outline">
                                <select class="js-example-basic-multiple js-example-basic-multiple-category"
                                    data-placeholder="Seleccione una categoría" name="id_category[]" multiple="multiple">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Category') }}</label>
                                <span id="id_categoryError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2" style="margin-bottom:25px;">
                                <a class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center"
                                    onClick="addCategory()" href="javascript:void(0)">
                                    Agregar Categoria
                                </a>
                            </div>
                            <div class="col-md-2 col-sm-2 form-outline">
                                <input name="cost" type="text" class="form-control" id="cost"
                                    placeholder="{{ __('Cost') }}" title="Es obligatorio un precio" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Cost') }}</label>
                                <span id="costError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input name="utility" type="text" class="form-control" id="utility"
                                    placeholder="{{ __('Utility') }}" title="Es obligatorio un descuento" minlength="1"
                                    value="0" maxlength="10" required onkeypress='return validaMonto(event)'>
                                <label class="form-label" for="form2Example17">{{ __('Utility') }} %</label>
                                <span id="utilityError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2 form-outline">
                                <input name="price" type="text" class="form-control" id="price"
                                    placeholder="{{ __('Price') }}" title="Es obligatorio un precio" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Price') }}</label>
                                <span id="priceError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
                                <select class="form-select" name="modal_currency_id" id="modal_currency_id">
                                    @foreach ($currencies as $currency)
                                        <option {{ $currency->is_principal == 1 ? 'selected' : '' }}
                                            value="{{ $currency->id }}">{{ $currency->abbreviation }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="modal_currency_id">Moneda</label>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
                                <input name="price2" type="text" class="form-control" id="price2"
                                    placeholder="{{ __('Price en moneda') }}" title="Es obligatorio un precio"
                                    minlength="1" maxlength="80" required autocomplete="off">
                                <label class="form-label" for="price2">{{ __('Price total en moneda') }}</label>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="url[]" type="file" class="form-control" id="url" multiple
                                    title="Es obligatorio una Imagen">
                                <label class="form-label" for="form2Example17">{{ __('Image') }}</label>
                                <span id="urlError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <select class="form-select" name="serial" id="serial">
                                    <option value="0">{{ __('NO') }}</option>
                                    <option value="1">{{ __('SI') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Serial') }}</label>
                                <span id="serialError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <select class="form-select" name="stock" id="stock">
                                    <option value="1">{{ __('SI') }}</option>
                                    <option value="0">{{ __('NO') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Stock') }}</label>
                                <span id="stockError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <input type="text" class="form-control" id="stock_min" name="stock_min"
                                    value="0" onkeypress='return validaNumericos(event)' required>
                                <label class="form-label" for="form2Example17">{{ __('Stock minimo') }}</label>
                                <span id="stock_minError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3">
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
    <!-- boostrap mostrar serial modal -->
    <div class="modal fade" id="serial-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="min-height: 600px !important;">
                <div class="modal-header">
                    <h5 class="modal-title" id="serial-modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="serial-product-id">
                    <input type="hidden" id="serial-quantity">
                    <div id="serial-warning" class="text-danger"></div>
                    <div id="serial-inputs"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="save-serials" class="btn btn-primary">Guardar seriales</button>
                </div>
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
    <script type="text/javascript">
        $(document).ready(function() {
            // Variables de tasas de cambio
            const currencies = @json($currencies);
            let tasaCambio = 1;
            const products = @json($products);
            let counter = 1;
            // Seriales temporales por producto
            window.serialesTemp = {};
            window.serialesPorEliminar = 0;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Unifica la moneda seleccionada en toda la vista
            function updateCurrencyAll(currencyId) {
                currencies.forEach(currency => {
                    if (currency.id == currencyId) {
                        tasaCambio = currency.rate2;
                    }
                });
                // Actualiza precios en la tabla
                $('.product tr').each(function() {
                    let priceInput = $(this).find('.price');
                    let price2Input = $(this).find('.price2');
                    let price = parseFloat(priceInput.val()) || 0;
                    price2Input.val((price * tasaCambio).toFixed(2));
                });
                // Actualiza precio en el modal si está abierto
                let priceModal = parseFloat($('#price').val()) || 0;
                $('#price2').val((priceModal * tasaCambio).toFixed(2));
                recalculateTotals();
            }
            // Initialize Select2
            $('.js-example-basic-multiple-products').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
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
            $('#selectProduct').hide();
            $('#divfraction').hide();
            // Product selection change event
            // Cuando cambie la moneda principal, actualiza todo
            $('#currency_id').on('change', function() {
                updateCurrencyAll($(this).val());
            });
            // Actualiza precios según moneda seleccionada en la tabla
            $('#currency_table_id').on('change', function() {
                let currencyId = $(this).val();
                currencies.forEach(currency => {
                    if (currency.id == currencyId) {
                        tasaCambio = currency.rate2;
                    }
                });
                $('.product tr').each(function() {
                    let priceInput = $(this).find('.price');
                    let price2Input = $(this).find('.price2');
                    let price = parseFloat(priceInput.val()) || 0;
                    price2Input.val((price * tasaCambio).toFixed(2));
                });
                recalculateTotals();
            });

            // Actualiza el precio2 en el modal según la moneda seleccionada
            $('#price').on('input', function() {
                let price = parseFloat($(this).val()) || 0;
                $('#price2').val((price * tasaCambio).toFixed(2));
            });

            // Actualiza el precio2 en la tabla principal según la moneda seleccionada
            $('.product').on('input', '.price', function() {
                let price = parseFloat($(this).val()) || 0;
                let price2Input = $(this).closest('tr').find('.price_bs');
                price2Input.val((price * tasaCambio).toFixed(2));
                recalculateTotals();
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
                            // Modo completo
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
            $('#price').on('input', function() {
                let selectedType = $('select[name=type]').val();
                if (selectedType !== 'SERVICIO') return;
                let price = parseFloat($(this).val()) || 0;
                let currencyId = $('#modal_currency_id').val();
                let tasaCambio = 1;
                currencies.forEach(currency => {
                    if (currency.id == currencyId) tasaCambio = currency.rate2;
                });
                let totalGeneral = parseFloat($('#service-integral-total').text()) || 0;
                let priceTotal = totalGeneral + (price * tasaCambio);
                $('#price2').val(priceTotal.toFixed(2));
            });


            $(document).on('change', '.js-example-basic-multiple-products', function(event) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('addProductShopping') }}",
                    data: {
                        id_product: $('.js-example-basic-multiple').val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        let tableBody = $('.product');
                        let selectedProducts = res.products || [];
                        let existingProducts = {};
                        tableBody.find('tr').each(function() {
                            let productId = $(this).find('input').data('id');
                            if (productId) {
                                existingProducts[productId] = $(this);
                            }
                        });
                        let counter = 1;
                        tableBody.find('tr.no-products-message').remove();
                        selectedProducts.forEach(product => {
                            let partes = product.name.match(/.{1,80}/g);
                            let name = partes.join("<br>");
                            let serialAttr = Number(product.serial) === 1 ?
                                'data-serial="1"' : '';
                            if (existingProducts[product.id]) {
                                let row = existingProducts[product.id];
                                row.find('td:eq(0)').text(counter++);
                                // ...actualiza otras celdas si lo necesitas
                            } else {
                                let serialIcon = '';
                                let cantidad = 0;
                                let cantidadInput = $(
                                    `.product tr[data-id="${product.id}"]`).find(
                                    '.quantity');
                                if (cantidadInput.length) {
                                    cantidad = parseInt(cantidadInput.val()) || 0;
                                }
                                if (Number(product.serial) === 1) {
                                    let serials = serialesTemp[product.id] || [];
                                    if (serials.length === 0) {
                                        serialIcon = `
                                <button type="button" class="btn btn-outline-danger btn-sm" title="Faltan seriales"
                                    onclick="showSerialModal(${product.id}, ${cantidad}, serialesTemp[${product.id}] || [])">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>`;
                                    } else if (serials.length < cantidad) {
                                        serialIcon = `
                                <button type="button" class="btn btn-outline-danger btn-sm" title="Faltan seriales"
                                    onclick="showSerialModal(${product.id}, ${cantidad}, serialesTemp[${product.id}] || [])">
                                    <i class="fa-solid fa-eye"></i>
                                </button>`;
                                    } else if (serials.length > cantidad) {
                                        serialIcon = `
                                <button type="button" class="btn btn-outline-danger btn-sm" title="Sobra seriales"
                                    onclick="showSerialModal(${product.id}, ${cantidad}, serialesTemp[${product.id}] || [])">
                                    <i class="fa-solid fa-eye"></i>
                                </button>`;
                                    } else {
                                        serialIcon = `
                                <button type="button" class="btn btn-outline-success btn-sm" title="Ver seriales"
                                    onclick="showSerialModal(${product.id}, ${cantidad}, serialesTemp[${product.id}] || [], true)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>`;
                                    }
                                } else {
                                    serialIcon = `
                            <button type="button" class="btn btn-outline-secondary btn-sm" title="Sin serial" disabled>
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>`;
                                }
                                let row = `
                        <tr data-id="${product.id}" ${serialAttr}>
                            <td style="text-align: center;">${counter++}</td>
                            <td style="text-align: center;">${product.code}</td>
                            <td>${name}</td>
                            <td class="center"><input type="text" name="quantity[]" value="0" class="form-control quantity" data-id="${product.id}" onkeypress='return validaNumericos(event)' style="width: 60px;"></td>
                            <td class="center"><input type="text" name="cost[]" value="${product.cost}" class="form-control cost" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 90px;"></td>
                            <td class="center"><input type="text" name="utility[]" value="${product.utility}" class="form-control utility" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 70px;"></td>
                            <td class="center"><input type="text" name="price[]" value="${product.price}" class="form-control price" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 100px;"></td>
                            <td class="center"><input type="text" name="price2[]" value="${(product.price * tasaCambio).toFixed(2)}" class="form-control price2" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 120px;"></td>
                            <td style="text-align:center">${serialIcon}</td>
                        </tr>
                    `;
                                tableBody.append(row);
                                updateSerialButton(product.id);
                            }
                        });
                        tableBody.find('tr').each(function() {
                            let productId = $(this).find('input').data('id');
                            if (productId && !selectedProducts.find(p => p.id ==
                                    productId)) {
                                $(this).remove();
                            }
                        });
                        recalculateTotals();
                    },
                    error: handleError
                });
            });

            // Evento input para cost, utility, price, price2 en la tabla
            $('.product').on('input', '.cost, .utility, .price, .price2', function() {
                const row = $(this).closest('tr');
                const costInput = row.find('.cost');
                const utilidadInput = row.find('.utility');
                const precioInput = row.find('.price');
                const precio2Input = row.find('.price2');
                let costo = parseFloat(costInput.val()) || 0;
                let utilidad = parseFloat(utilidadInput.val()) || 0;
                let precio = parseFloat(precioInput.val()) || 0;
                let precio2 = parseFloat(precio2Input.val()) || 0;

                if ($(this).hasClass('cost') || $(this).hasClass('utility')) {
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
                } else if ($(this).hasClass('price')) {
                    if (costo !== 0) {
                        utilidad = 100 - ((costo * 100) / precio);
                        utilidadInput.val(utilidad > 99.99 ? 99.99 : utilidad < 0 ? 0.00 : utilidad.toFixed(
                            2));
                    } else {
                        utilidadInput.val(0.00);
                    }
                    precio2 = precio * tasaCambio;
                    precio2Input.val(precio2.toFixed(2));
                } else if ($(this).hasClass('price2')) {
                    if (tasaCambio !== 0) {
                        precio = precio2 / tasaCambio;
                        precioInput.val(precio.toFixed(2));
                        if (costo !== 0) {
                            utilidad = 100 - ((costo * 100) / precio);
                            utilidadInput.val(utilidad > 99.99 ? 99.99 : utilidad < 0 ? 0.00 : utilidad
                                .toFixed(2));
                        } else {
                            utilidadInput.val(0.00);
                        }
                    } else {
                        precioInput.val('');
                    }
                }
                recalculateTotals();
            });
            // Evento para abrir el modal de seriales al cambiar cantidad
            $('.product').on('change', '.quantity', function() {
                const row = $(this).closest('tr');
                const productId = $(this).data('id');
                const quantity = parseInt($(this).val()) || 0;
                const hasSerial = row.data('serial') == 1;
                if (hasSerial) {
                    let serials = serialesTemp[productId] || [];
                    showSerialModal(productId, quantity, serials);
                    updateSerialButton(productId);
                }
                recalculateTotals(); // <-- Agrega esta línea
            });
            // Mostrar modal de seriales
            function showSerialModal(productId, quantity, serials, viewOnly = false) {
                const product = products.find(p => p.id == productId);
                $('#serial-modal-title').html(`<b>${product.code}</b> - ${product.name}`);
                $('#serial-product-id').val(productId);
                $('#serial-quantity').val(quantity);

                let html = '<table class="table"><thead><tr><th>#</th><th>Serial</th>';
                // Mostrar columna eliminar si hay que eliminar seriales
                if (!viewOnly && serials.length > quantity) html += '<th>Eliminar</th>';
                html += '</tr></thead><tbody>';
                if (!viewOnly && serials.length > quantity) {
                    window.serialesPorEliminar = serials.length - quantity;
                } else {
                    window.serialesPorEliminar = 0;
                }
                let eliminarRestantes = serials.length - quantity;
                serials.forEach((serial, idx) => {
                    html +=
                        `<tr id="serial-row-${idx}">
        <td>${idx + 1}</td>
        <td><input type="text" class="form-control serial-input" value="${serial}" data-idx="${idx}" ${viewOnly ? 'readonly' : ''}></td>`;
                    if (!viewOnly && serials.length > quantity) {
                        html +=
                            `<td><button type="button" class="btn btn-danger btn-sm" onclick="deleteSerialInput(${idx})" ${window.serialesPorEliminar <= 0 ? 'disabled' : ''}>Eliminar</button></td>`;
                    }
                    html += `</tr>`;
                });

                // Si faltan seriales, agregar inputs vacíos
                if (!viewOnly && serials.length < quantity) {
                    for (let i = serials.length; i < quantity; i++) {
                        html += `<tr>
                <td>${i + 1}</td>
                <td><input type="text" class="form-control serial-input" value="" data-idx="${i}"></td>
                <td></td>
            </tr>`;
                    }
                }

                html += '</tbody></table>';

                // Mensaje de advertencia si hay que eliminar seriales
                if (!viewOnly && serials.length > quantity) {
                    $('#serial-warning').text('Debes eliminar ' + (serials.length - quantity) +
                        ' serial(es) para igualar la cantidad.');
                } else {
                    $('#serial-warning').text('');
                }

                $('#serial-inputs').html(html);
                $('#serial-modal').modal('show');

                // Mostrar/ocultar botón guardar
                if (viewOnly) {
                    $('#save-serials').hide();
                } else {
                    $('#save-serials').show();
                }
            }

            // Eliminar serial input del modal (solo si hay sobrantes)
            window.deleteSerialInput = function(idx) {
                if (window.serialesPorEliminar > 0) {
                    const productId = $('#serial-product-id').val();
                    let serials = serialesTemp[productId] || [];
                    serials.splice(idx, 1);
                    serialesTemp[productId] = serials;
                    window.serialesPorEliminar--;
                    const quantity = parseInt($('#serial-quantity').val()) || 0;
                    showSerialModal(productId, quantity, serials);
                }
            }

            // Guardar seriales del modal en JS
            $('#save-serials').on('click', function() {
                const productId = $('#serial-product-id').val();
                let serials = [];
                let valid = true;
                $('.serial-input').each(function() {
                    if ($(this).val().trim() === '') {
                        valid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                    serials.push($(this).val());
                });
                if (!valid) {
                    $('#serial-warning').text('Todos los seriales son requeridos.');
                    return;
                }
                serialesTemp[productId] = serials;
                $('#serial-modal').modal('hide');
                updateSerialButton(productId);
            });
            // Evento para abrir el modal solo para ver seriales (icono ojo)
            window.showSerialModal = showSerialModal;

            function updateSerialButton(productId) {
                let cantidadInput = $(`.product tr[data-id="${productId}"]`).find('.quantity');
                let cantidad = cantidadInput.length ? parseInt(cantidadInput.val()) || 0 : 0;
                let serials = serialesTemp[productId] || [];
                let serialIcon = '';
                if (serials.length === 0) {
                    serialIcon = `
             <button type="button" class="btn btn-outline-secondary btn-sm" title="Sin serial" disabled>
                <i class="fa-solid fa-eye-slash"></i>
            </button>`;
                } else if (serials.length < cantidad) {
                    serialIcon = `
            <button type="button" class="btn btn-outline-danger btn-sm" title="Faltan seriales"
                onclick="showSerialModal(${productId}, ${cantidad}, serialesTemp[${productId}] || [])">
                <i class="fa-solid fa-eye"></i>
            </button>`;
                } else if (serials.length > cantidad) {
                    serialIcon = `
            <button type="button" class="btn btn-outline-danger btn-sm" title="Sobra seriales"
                onclick="showSerialModal(${productId}, ${cantidad}, serialesTemp[${productId}] || [])">
                <i class="fa-solid fa-eye"></i>
            </button>`;
                } else {
                    serialIcon = `
            <button type="button" class="btn btn-outline-success btn-sm" title="Ver seriales"
                onclick="showSerialModal(${productId}, ${cantidad}, serialesTemp[${productId}] || [], true)">
                <i class="fa-solid fa-eye"></i>
            </button>`;
                }
                $(`.product tr[data-id="${productId}"] td:last`).html(serialIcon);
            }

            function recalculateTotals() {
                let totalQuantity = 0;
                let totalCost = 0;
                let totalUtility = 0;
                let totalPrice = 0;
                let totalPrice2 = 0;
                let total = 0;
                $('.product tr').each(function() {
                    let rowQuantity = parseFloat($(this).find('.quantity').val()) || 0;
                    let rowCost = parseFloat($(this).find('.cost').val()) || 0;
                    let rowUtility = parseFloat($(this).find('.utility').val()) || 0;
                    let rowPrice = parseFloat($(this).find('.price').val()) || 0;
                    let rowPrice2 = parseFloat($(this).find('.price2').val()) || 0;
                    total += (rowCost * rowQuantity);
                    totalCost += (rowCost * rowQuantity);
                    totalUtility += rowUtility;
                    totalPrice += (rowPrice * rowQuantity);
                    totalPrice2 += (rowPrice2 * rowQuantity);
                });
                const totalId = $('#total');
                totalId.val(total);
                updateTotals(totalCost, totalUtility, totalPrice, totalPrice2);

            }

            function updateTotals(totalCost, totalUtility, totalPrice, totalPrice2) {
                $('#totalCost').text(totalCost.toFixed(2));
                $('#totalUtility').text(totalUtility.toFixed(2));
                $('#totalPrice').text(totalPrice.toFixed(2));
                $('#totalPrice2').text(totalPrice2.toFixed(2));
            }
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
                        let cantidad = cantidadesPrevias[productId] || 1;
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

            function handleError(error) {
                if (error) {
                    console.error("AJAX Error:", error); // More descriptive error message
                    if (error.responseJSON && error.responseJSON.errors) {
                        console.log("Validation Errors:", error.responseJSON.errors);
                    }
                }
            }
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
                    $('.error-messages').html('');

                    // Antes de enviar, agrega los valores de is_fraction por producto fraccionado
                    // Busca todos los selects de modo fraccion y agrega un input oculto por cada uno
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

                    var formData = new FormData(this);
                    $.ajax({
                        type: 'POST',
                        url: "{{ url('storeProduct') }}",
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: (data) => {
                            const newProductData = data;
                            products.push(newProductData);

                            $("#product-modal").modal('hide');
                            let newOption = new Option(
                                newProductData.code + " " + newProductData.name,
                                newProductData.id,
                                true,
                                true
                            );
                            $('.js-example-basic-multiple-products').append(newOption).trigger(
                                'change');
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
                                $('#costError').html(error.responseJSON.errors.cost);
                                $('#utilityError').html(error.responseJSON.errors.utility);
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
            $('#shoppingForm').submit(function(event) {
                event.preventDefault();
                // Seriales por producto (ya guardados en serialesTemp)
                let serials = serialesTemp;

                // Obtener datos del formulario
                const id_inventory = $('#id_inventory').val();
                const codeBill = $('#codeBill').val();
                const date = $('#date').val();
                const nameProvider = $('#name').val();
                const totalBill = $('#total').val();
                const selectedProducts = $('.js-example-basic-multiple-products').val();

                // Obtener datos de la tabla de productos
                const productsTableData = [];
                $('.product tr').each(function() {
                    const productId = $(this).find('.quantity').data('id');
                    const quantity = $(this).find('.quantity').val();
                    const cost = $(this).find('.cost').val();
                    const utility = $(this).find('.utility').val();
                    const price = $(this).find('.price').val();
                    const price2 = $(this).find('.price2').val();
                    if (productId) {
                        productsTableData.push({
                            id: productId,
                            quantity: quantity,
                            cost: cost,
                            utility: utility,
                            price: price,
                            price2: price2
                        });
                    }
                });

                // Crear objeto con todos los datos
                const datas = {
                    id_inventory: id_inventory,
                    codeBill: codeBill,
                    date: date,
                    nameProvider: nameProvider,
                    totalBill: totalBill,
                    selectedProducts: selectedProducts, // Productos del select2
                    productsTableData: productsTableData, // Productos de la tabla
                    currency_id: $('#currency_table_id').val(),
                    serials: JSON.stringify(serials)
                };
                // Enviar solicitud AJAX
                $.ajax({
                    url: "{{ route('storeShopping') }}", // Usar la ruta con nombre
                    type: 'POST',
                    data: datas,
                    dataType: 'json', // Esperar respuesta en formato JSON
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Incluir token CSRF
                    },
                    success: function(response) {
                        // Manejar la respuesta del servidor (ej: mostrar mensaje de éxito)
                        console.log(response);
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "{{ __('Log saved successfully') }}",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#shoppingForm')[0].reset(); // Limpiar el formulario
                        $('.js-example-basic-multiple-products').val(null).trigger(
                            'change'); // Limpiar select2
                        $('.product').empty(); // Limpiar la tabla
                        $('.product').append(
                            '<tr class="no-products-message"><td style="text-align: center;" colspan="8">No hay productos seleccionados.</td></tr>'
                        );
                        updateTotals(0, 0, 0, 0);
                        const pdfLink = "{{ route('pdfShopping', ':id') }}".replace(':id',
                            response.id);
                        // Create a temporary anchor element for the click even
                        window.open(pdfLink, '_blank');
                    },
                    error: function(error) {
                        // Manejar errores de la solicitud
                        console.error(error);
                        alert('Error al registrar la compra. Por favor, inténtelo de nuevo.');
                        if (error.responseJSON && error.responseJSON.errors) {
                            // Mostrar errores de validación en el formulario
                            $.each(error.responseJSON.errors, function(key, value) {
                                $('#' + key + 'Error').text(value[
                                    0
                                ]); // Mostrar el primer mensaje de error para cada campo.
                                $('#' + key).addClass(
                                    'is-invalid'); // Agregar clase de error al input
                            });
                        }
                    }
                });
            });


            function deleteSerial(indexId) {
                $('#tr' + indexId).html('');
            }


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
                        var oTable = $('#ajax-crud-datatable').dataTable();
                        oTable.fnDraw(false);
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
        });

        function add() {
            $.ajax({
                type: "POST",
                url: "{{ url('codeProduct') }}",
                success: function(res) {
                    $('#selectProduct').hide();
                    $('#productForm').trigger("reset");
                    $('#description').html('');
                    $('.error-messages').html('');
                    $('#product-modal').modal('show');
                    $('#id').val('');
                    $('#code').val(res);
                    $('#divdropzone').hide();
                    $('#divadd').show();
                    $('#selectProduct').hide();
                    $('#divfraction').hide();
                    $('#name_fraction').val('');
                    $('#price_fraction').val('');
                    $('#equivalence_fraction').val(1);
                    $('#type').val('NORMAL').trigger('change');
                }
            });
        }

        function addCategory() {
            $('#categoryForm').trigger("reset");
            $('.error-messages').html('');
            $('#category-modal').modal('show');
            $('#id_category').val('');
        }
    </script>
@endsection
