@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'store'])
@section('content')
    <style>
        .margen {
            margin-top: 40px !important;
        }
        @media (max-width: 767.98px) {
            .margen {
                margin-top: 80px !important;
            }
        }
        @media (max-width: 575.98px) {
            .margen {
                margin-top: 90px !important;
            }
        }
    </style>
    <div class="container-fluid margen " style="">
        <div class="row justify-content-center productBuscador">
        </div>
    </div>
    <!-- boostrap mostrar product modal -->
    <div class="modal fade" id="product-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Product Detail') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-5 col-sm-5 col-6 " id="divcarousel">
                        </div>
                        <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-7 col-sm-7 col-6">
                            <h6 id="divname"></h6>
                            <P id="divprice"></P>
                            <p id="divdescription" style="font-size: 13px"></p>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <div class="input-group">
                                        <input class="form-control" id="existencia" disabled>
                                        <select id="modalInventorySelect" class="form-select" style="width: 120px; margin-left:10px;">
                                            <option value="all">Todos</option>
                                            @foreach($inventories as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="form-label" for="form2Example17">{{ __('Existencia en inventario') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
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
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var id_inventoryStore = $('#id_inventoryStore').val();
            var sortBy = $('#sort-by-stock').val();
            var id_currencyStore = $('#id_currencyStore').val();

            $.ajax({
                type: "POST",
                url: "{{ route('indexStoreAjax') }}",
                data: {
                    page: $(this).attr('href').split('page=')[1],
                    category: category,
                    scope: scope,
                    id_inventory: id_inventoryStore,
                    sort_by: sortBy,
                    id_currencyStore: id_currencyStore
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('.productBuscador').html(res);
                    window.scrollTo(0, 0);
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        });
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('mouseenter', '.card-integral, .card-fraccionado', function() {
                let id = $(this).data('id');
                let type = $(this).data('type');
                showProductInfo(id, type, this);
            });
            $(document).on('mouseleave', '.card-integral, .card-fraccionado', function() {
                hideProductInfo(this);
            });
            $('#category').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                height: '20px',
                dropdownCssClass: "cate",
                selectionCssClass: "catego",
                language: "es"
            });
        });
        function mostrarProduct(id) {
            var id_inventory = $('#id_inventoryStore').val();
            var id_currencyStore = $('#id_currencyStore').val();
            $.ajax({
                type: "POST",
                url: "{{ url('mostrarProduct') }}",
                data: {
                    id: id,
                    id_inventory: id_inventory,
                    id_currencyStore: id_currencyStore
                },
                dataType: 'json',
                success: function(res) {
                    $('#product-modal').modal('show');
                    $('#divname').html(res.product.name);
                    // Precio en moneda principal
                    let pricePrincipal = parseFloat(res.product.price).toFixed(2);
                    let abbrPrincipal = res.currencyPrincipal.abbreviation;
                    // Precio en moneda seleccionada
                    let priceSelected = '';
                    let abbrSelected = '';
                    if (res.currencySelected.id == res.currencyPrincipal.id) {
                        // Si la seleccionada es la principal, busca la oficial (is_official = 1 y distinta de principal)
                        let official = res.currencySelected.is_official == 1 ? res.currencySelected : null;
                        console.log(official);
                        if (!official || official.id == res.currencyPrincipal.id) {
                            // No hay otra moneda oficial distinta, solo muestra el principal
                            priceSelected = (parseFloat(res.product.price) * parseFloat(res.currencyOfficial.rate)).toFixed(2);
                            abbrSelected = res.currencyOfficial.abbreviation;
                        } else {
                            priceSelected = '';
                        }
                    } else {
                        // Si la seleccionada NO es principal, calcula el precio en esa moneda
                        priceSelected = (parseFloat(res.product.price) * parseFloat(res.currencySelected.rate2)).toFixed(2);
                        abbrSelected = res.currencySelected.abbreviation;
                        pricePrincipal = (priceSelected / res.currencySelected.rate).toFixed(2); // Recalcula el precio principal para mostrarlo correctamente
                    }
                    let priceHtml = '';
                    priceHtml += `<p style="margin-top: 12px; font-size: 15px; margin:0px;">
                        Precio en <span><b>${abbrPrincipal} ${pricePrincipal}</b></span>
                    </p>`;
                    if (priceSelected && abbrSelected) {
                        priceHtml += `<p style="margin-top: 12px; font-size: 15px; margin:0px;">
                            Precio en <span><b>${abbrSelected} ${priceSelected}</b></span>
                        </p>`;
                    }
                    priceHtml += `<p style="margin-top: 12px; font-size: 13px; margin:0px;">
                        Cod. <span><b>${res.product.code}</b></span>
                    </p>`;
                    $('#divprice').html(priceHtml);

                    $('#divdescription').html(res.product.description);
                    // Contenedor del carrusel
                    var carouselHtml = '<div id="carouselExampleIndicators' + res.product.id +
                        '" class="carousel slide" data-bs-ride="carousel" style="border-radius: 1rem 0 0 1rem; height:100% !important;">' +
                        '<div class="carousel-indicators" id="buttonCarousel">' +
                        '</div>' +
                        '<div class="carousel-inner" id="itemCarousel">' +
                        '</div>' +
                        '<button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators' +
                        res.product.id + '" data-bs-slide="prev">' +
                        '<span class="carousel-control-prev-icon" aria-hidden="true"></span>' +
                        '<span class="visually-hidden">Previous</span>' +
                        '</button>' +
                        '<button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators' +
                        res.product.id + '" data-bs-slide="next">' +
                        '<span class="carousel-control-next-icon" aria-hidden="true"></span>' +
                        '<span class="visually-hidden">Next</span>' +
                        '</button>' +
                        '</div>';
                    $('#divcarousel').html(carouselHtml);
                    // Construir los elementos del carrusel según la cantidad de imágenes
                    var imageCount = 0;
                    let urls = "'{{ asset('storage/products/product.png') }}'";
                    if (res.product.url1 != '' && res.product.url1 != null) {
                        imageCount++;
                        $('#buttonCarousel').append(
                            '<button type="button" data-bs-target="#carouselExampleIndicators' + res.product
                            .id + '" data-bs-slide-to="0" class="' + (imageCount === 1 ? 'active' : '') +
                            '" aria-current="true" aria-label="Slide 1"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item ' + (imageCount === 1 ? 'active' : '') +
                            '" data-bs-interval="3000">' +
                            '<img src="/storage/' + res.product.url1 + '" onerror="this.src=' + urls +
                            '" class="d-block w-100" alt="..." style="border-radius: 1rem 0 0 1rem; height:280px !important;">' +
                            '</div>'
                        );
                    }
                    if (res.product.url2 != '' && res.product.url2 != null) {
                        imageCount++;
                        $('#buttonCarousel').append(
                            '<button type="button" data-bs-target="#carouselExampleIndicators' + res.product
                            .id + '" data-bs-slide-to="1" aria-label="Slide 2"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item" data-bs-interval="3000">' +
                            '<img src="/storage/' + res.product.url2 + '" onerror="this.src=' + urls +
                            '" class="d-block w-100" alt="..." style="border-radius: 1rem 0 0 1rem; height:280px !important;">' +
                            '</div>'
                        );
                    }
                    if (res.product.url3 != '' && res.product.url3 != null) {
                        imageCount++;
                        $('#buttonCarousel').append(
                            '<button type="button" data-bs-target="#carouselExampleIndicators' + res.product
                            .id + '" data-bs-slide-to="2" aria-label="Slide 3"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item" data-bs-interval="3000">' +
                            '<img src="/storage/' + res.product.url3 + '" onerror="this.src=' + urls +
                            '" class="d-block w-100" alt="..." style="border-radius: 1rem 0 0 1rem; height:280px !important;">' +
                            '</div>'
                        );
                    }
                    if (res.product.url1 == '' && res.product.url2 == '' && res.product.url3 == '') {
                        console.log('no hay imagen');
                        imageCount++;
                        $('#buttonCarousel').append(
                            '<button type="button" data-bs-target="#carouselExampleIndicators' + res.product
                            .id + '" data-bs-slide-to="0" class="' + (imageCount === 1 ? 'active' : '') +
                            '" aria-current="true" aria-label="Slide 1"></button>'
                        );
                        $('#itemCarousel').append(
                            '<div class="carousel-item ' + (imageCount === 1 ? 'active' : '') +
                            '" data-bs-interval="3000">' +
                            '<img src="/storage/products/product.png"  class="d-block w-100" alt="..." style="border-radius: 1rem 0 0 1rem; height:280px !important;">' +
                            '</div>'
                        );
                    }

                    let stockValue = parseFloat(res.product.stock);
                    if (isNaN(stockValue)) stockValue = 0;
                    $('#existencia').val(stockValue.toFixed(2));
                    $('#modalInventorySelect').val(id_inventory); // Selecciona el inventario actual

                    $('#modalInventorySelect').off('change').on('change', function() {
                        let selectedInv = $(this).val();
                        $.ajax({
                            type: "POST",
                            url: "{{ url('mostrarProduct') }}",
                            data: {
                                id: res.product.id,
                                id_inventory: selectedInv,
                                id_currencyStore: id_currencyStore
                            },
                            dataType: 'json',
                            success: function(r) {
                                let stockValue = parseFloat(r.product.stock);
                                if (isNaN(stockValue)) stockValue = 0;
                                $('#existencia').val(stockValue.toFixed(2));
                            }
                        });
                    });
                    $('#id_product').val(res.product.id);
                    $('#price').val(res.price);

                    // Inicializar el carrusel solo si hay al menos una imagen
                    if (imageCount > 0) {
                        $('#carouselExampleIndicators' + res.product.id + '').carousel({
                            interval: 3000 // Cambiado a 3000 para que se vea mejor
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

        function filtro() {
            var tecla = event.key;
            if (['.', ',', 'e', '-', '+'].includes(tecla)) {
                event.preventDefault()
            }
        }

        function refreshProductList() {
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var id_inventoryStore = $('#id_inventoryStore').val();
            var sortBy = $('#sort-by-stock').val();
            var id_currencyStore = $('#id_currencyStore').val(); // <-- Añade esto

            $.ajax({
                type: "POST",
                url: "{{ route('indexStoreAjax') }}",
                data: {
                    category: category,
                    scope: scope,
                    id_inventory: id_inventoryStore,
                    sort_by: sortBy,
                    id_currencyStore: id_currencyStore // <-- Añade esto
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('.productBuscador').html(res);
                },
                error: function(error) {
                    if (error) {
                        console.error("Error en la solicitud AJAX:", error.responseJSON || error);
                    }
                }
            });
        }

        // Opcional: Ejecutar al cargar la página para mostrar los productos iniciales con filtros por defecto
        document.addEventListener('DOMContentLoaded', function() {
            refreshProductList();
        });

        function downloadCatalog() {
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var sortBy = $('#sort-by-stock').val();
            var id_inventory = $('#id_inventoryStore').val(); // <-- Obtener el valor del select de inventario

            var url = "{{ url('pdfCatalog') }}" +
                "&category=" + category +
                "&scope=" + scope +
                "&sort_by=" + sortBy +
                "&id_inventory=" + id_inventory; // <-- Añadir id_inventory a la URL

            window.open(url, '_blank');
        }
        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 121 && !event.ctrlKey && !event.altKey && !event.shiftKey) {
                // Prevenir el comportamiento por defecto de la tecla F10 (ej. abrir menú de depuración en algunos navegadores)
                event.preventDefault();
                window.location.href = "{{ route('indexBilling') }}";
            }
        });
        function showProductInfo(id, type, cardElem) {
            // Obtén el id de la moneda seleccionada del select
            let id_currencyStore = $('#id_currencyStore').val();

            $.ajax({
                type: "POST",
                url: type === 'INTEGRAL' ? "{{ url('getIntegralInfo') }}" : (type === 'SERVICIO' ? "{{ url('getIntegralInfo') }}" : "{{ url('getFractionInfo') }}"),
                data: { id: id, id_currencyStore: id_currencyStore },
                dataType: 'json',
                success: function(res) {
                    let html = '';
                    // Calcula el precio en la moneda seleccionada
                    let abbrSel = res.currencySelected.abbreviation;
                    let rateSel = parseFloat(res.currencySelected.rate);
                    let rate2Sel = parseFloat(res.currencySelected.rate2);

                    if(type === 'SERVICIO') {
                        html += `<div style="background:#e3f2fd; padding:10px; border-radius:8px; min-width:220px;">
                            <b>Productos integrados:</b><br>`;
                        let total = res.product.price;
                        let totalSel = res.product.price * rate2Sel;
                        res.integrals.forEach(function(item) {
                            let priceSel = item.price * rate2Sel;
                            html += `- ${item.name} x${item.quantity} <span style="float:right;">${priceSel.toFixed(2)} ${abbrSel}</span><br>`;
                            totalSel += priceSel * item.quantity;
                        });
                        html += `<hr><b>Total integral: ${totalSel.toFixed(2)} ${abbrSel}</b></div>`;
                    } else if(type === 'INTEGRAL') {
                        html += `<div style="background:#e3f2fd; padding:10px; border-radius:8px; min-width:220px;">
                            <b>Productos integrados:</b><br>`;
                        let total = res.product.price;
                        let totalSel = 0;
                        res.integrals.forEach(function(item) {
                            let priceSel = item.price * rate2Sel;
                            html += `- ${item.name} x${item.quantity} <span style="float:right;">${priceSel.toFixed(2)} ${abbrSel}</span><br>`;
                            totalSel += priceSel * item.quantity;
                        });
                        html += `<hr><b>Total integral: ${totalSel.toFixed(2)} ${abbrSel}</b></div>`;
                    } else if(type === 'FRACCIONADO') {
                        let priceFractionSel = res.product.price_fraction * rate2Sel;
                        html += `<div style="background:#fffde7; padding:10px; border-radius:8px; min-width:220px;">
                            <b>Fracción:</b> ${res.product.name_fraction}<br>
                            <b>Equivalencia:</b> ${res.product.equivalence_fraction}<br>
                            <b>Precio fracción:</b> ${priceFractionSel.toFixed(2)} ${abbrSel}
                        </div>`;
                    }
                    $(cardElem).find('.product-info-popup').html(html).show();
                }
            });
        }
        function hideProductInfo(cardElem) {
            $(cardElem).find('.product-info-popup').hide();
        }
    </script>
@endsection
