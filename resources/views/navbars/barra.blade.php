<style>
    .navbar-main {
        background: #fff !important;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.07);
        border-radius: 1.5rem;
        position: fixed;
        top: 0;
        left: 250px;
        width: calc(100% - 250px);
        z-index: 1000;
        padding: 0.8rem 1.5rem;
        min-height: 50px;
        display: flex;
        align-items: center;
    }

    .breadcrumb {
        font-size: 0.8rem;
        margin-bottom: 0;
        background: transparent;
        color: #2196f3 !important;
        font-weight: 600;
    }

    .breadcrumb-item {
        color: #2196f3 !important;
        font-weight: 600;
    }

    .breadcrumb-user {
        color: #00bcd4 !important;
        font-weight: 600;
    }

    .btn-barra,
    .btn-danger2 {
        font-size: 1.08rem !important;
        padding: 6px 18px !important;
        border-radius: 0.75rem !important;
        margin-left: 10px !important;
        background: linear-gradient(90deg, #2196f3 0%, #00bcd4 100%) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.10);
        border: none !important;
        font-weight: 600 !important;
        transition: background 0.2s, box-shadow 0.2s;
    }

    .btn-barra:hover,
    .btn-danger2:hover{
        background: linear-gradient(90deg, #1565c0 0%, #0097a7 100%) !important;
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(33, 150, 243, 0.18);
    }

    .navbar-nav {
        flex-direction: row !important;
        align-items: center;
        gap: 0.7rem;
    }

    .navbar-nav .nav-item {
        margin: 0 !important;
    }

    .sidebar-toggle-btn {
        display: none;
    }

    @media (max-width: 991px) {
        .navbar-nav {
            margin-left: auto !important;
            flex-direction: row !important;
            justify-content: flex-end !important;
            width: auto !important;
        }

        .navbar-main {
            left: 0;
            width: 100%;
            border-radius: 1rem;
            padding: 0.5rem 0.5rem;
            min-height: 56px;
            flex-direction: column;
            align-items: stretch;
        }

        .container-fluid {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem !important;
        }

        .sidebar-toggle-btn {
            display: inline-block !important;
        }
        .btn-sm {
            padding: 0.3rem 0.4rem !important;
        }
        i {
            font-size: 0.9rem !important;
        }
        .breadcrumb-item{
            font-size: 0.7rem !important;
        }
    }
</style>
<nav class="navbar navbar-main navbar-expand-lg shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-0 px-0 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex flex-wrap align-items-center w-100" style="gap: 1rem;">
            <nav aria-label="breadcrumb" class="me-2 flex-grow-1">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-0 px-0">
                    <li class="breadcrumb-item">{{ $pageTitles[$pageSlug] ?? 'Pagina' }}</li>
                    <li class="breadcrumb-item breadcrumb-user">{{ auth()->user()->type }}</li>
                    <li class="breadcrumb-item breadcrumb-user">{{ auth()->user()->name }}</li>
                </ol>
            </nav>

            <ul class="navbar-nav justify-content-end align-items-center flex-wrap ms-auto" style="flex-wrap:nowrap;">
                @if ($pageSlug == 'store')
                    <li class="nav-item">
                        <a onclick="downloadCatalog()" class="btn btn-danger2 btn-sm" title="Descargar catálogo">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <button class="sidebar-toggle-btn btn btn-danger2 d-block d-lg-none btn-sm" id="sidebarToggle"
                        title="Menú">
                        <i class="fa fa-bars"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger2 btn-sm" href="{{ route('logout') }}" title="Salir">
                        <i class="fa fa-power-off"></i>
                    </a>
                </li>
            </ul>
            @if ($pageSlug == 'store')
                <div class="row g-2 w-100 align-items-center buscadorCategorias">
                    <div class="col-6 col-sm-6 col-md-2">
                        <select id="sort-by-stock" class="form-select" onchange="refreshProductList()">
                            <option value="date">Por fecha</option>
                            <option value="asc">Menor a Mayor Existencia</option>
                            <option value="desc">Mayor a Menor Existencia</option>
                            <option value="available">Existencia Mayor a 0</option>
                            <option value="unavailable">Existencia Menor a 1</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <select onChange="refreshProductList()" class="form-select catego cate" name="id_category"
                            id="category" data-placeholder="{{ __('Categories') }}">
                            <option></option>
                            <option value="TODAS">{{ __('ALL') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4">
                        <input id="buscador" type="text" class="form-control buscar"
                            placeholder="{{ __('Buscar...') }}" onKeyup="refreshProductList()">
                    </div>
                    <div class="col-3 col-sm-3 col-md-2">
                        <select class="form-select" name="id_inventoryStore" id="id_inventoryStore"
                            onchange="refreshProductList()">
                            <option value="all">{{ __('Todos') }}</option>
                            @foreach ($inventories as $inventory)
                                <option value="{{ $inventory->id }}">{{ $inventory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3 col-sm-3 col-md-2">
                    <select id="id_currencyStore" name="id_currencyStore" class="form-select"  onchange="refreshProductList()">
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}"
                                {{ $currency->is_official == 1 ? 'selected' : '' }}>
                                {{ $currency->abbreviation }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                </div>
            @endif
        </div>

    </div>
</nav>
<script>
    // Mostrar/ocultar sidebar en móvil
    document.querySelectorAll('#sidebarToggle').forEach(function(btn) {
        btn.onclick = function() {
            var sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        };
    });
    // Opcional: ocultar sidebar al hacer click fuera en móvil
    document.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebarMenu');
        var toggleBtns = document.querySelectorAll('#sidebarToggle');
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(e.target) && !Array.from(toggleBtns).some(btn => btn.contains(e.target))) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>
