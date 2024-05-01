<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header"  style="text-align: center !important; " >
      <a  @if (auth()->user()->type != "CLIENTE" ) href="{{ route('dashboard') }}" @else href="#"  @endif style="width: 50px !important; height: 50px !important; margin:auto !important; " >
        <img src="../assets/img/DIGI.png"  style=" padding:0px; width: 80% !important; height: 90px !important; margin:auto !important;" >
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main" style="height: 85% !important; ">
      <ul class="navbar-nav" >
        <li class="nav-item">
          <a class="nav-link @if($pageSlug == 'store') active  @endif" href="{{ route('storeIndex') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fa-solid fa-store text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Tienda</span>
          </a>
        </li>  
        @if (auth()->user()->type != "CLIENTE")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'order') active  @endif" href="{{ route('indexOrder') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-cart-flatbed-suitcase text-danger text-sm opacity-10" ></i>
              </div>
              <span class="nav-link-text ms-1">Pedidos</span>
            </a>
          </li> 
        @endif
        @if (auth()->user()->type == "CLIENTE")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'orderClient') active  @endif" href="{{ route('indexOrderClient') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-cart-flatbed-suitcase text-danger text-sm opacity-10" ></i>
              </div>
              <span class="nav-link-text ms-1">Mis pedidos</span>
            </a>
          </li> 
        @endif
        @if (auth()->user()->type == "ADMINISTRADOR")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'category') active  @endif  @if($pageSlug == 'addCategory') active  @endif"  role="button" data-bs-toggle="collapse" href="#submenucategory">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-table-list text-danger text-sm opacity-10" style=" color:purple !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Categorias</span>
            </a>
            <ul class="collapse nav flex-column ms-1" id="submenucategory" data-bs-parent="#menu">
                <li class="w-100 ">
                    <a href="{{ route('indexCategory') }}" class="nav-link @if($pageSlug == 'category') active  @endif"> <span class="d-none d-sm-inline">Crear</span></a>
                </li>
                <li>
                    <a href="{{ route('indexAddCategory') }}" class="nav-link @if($pageSlug == 'addCategory') active  @endif"> <span class="d-none d-sm-inline">Agregar</span></a>
                </li>
            </ul>
          </li>
        @endif
        @if (auth()->user()->type == "EMPRESA")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'addCategory') active  @endif" href="{{ route('indexAddCategory') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-table-list text-danger text-sm opacity-10" style=" color:purple !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Categorias</span>
            </a>
          </li>
        @endif
        @if (auth()->user()->type == "EMPRESA" || auth()->user()->type == "ADMINISTRADOR")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'product') active  @endif" href="{{ route('indexProduct') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-app  text-sm opacity-10" style=" color:orange !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Productos</span>
            </a>
          </li>
        @endif
        @if (auth()->user()->type == "EMPRESA")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'userEmployee') active  @endif" href="{{ route('indexEmployee') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-user-astronaut text-sm opacity-10" style=" color:green !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Empleados</span>
            </a>
          </li>
        @endif
        @if (auth()->user()->type == "EMPRESA")
          <li class="nav-item">
            <a class="nav-link @if($pageSlug == 'userClient') active  @endif" href="{{ route('indexClient') }}">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-user text-sm opacity-10" style=" color:green !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Clientes</span>
            </a>
          </li>
        @endif
     
        @if (auth()->user()->type == "ADMINISTRADOR")
          <li >
            <a class="nav-link @if($pageSlug == 'userCompany') active  @endif @if($pageSlug == 'userEmployee') active  @endif @if($pageSlug == 'userClient') active  @endif" role="button" data-bs-toggle="collapse" href="#submenu">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-user-astronaut text-sm opacity-10" style=" color:green !important;"></i>
              </div>
              <span class="nav-link-text ms-1">Usuarios</span>
            </a>
            <ul class="collapse nav flex-column ms-1" id="submenu" data-bs-parent="#menu">
                <li class="w-100 ">
                    <a href="{{ route('indexCompany') }}" class="nav-link @if($pageSlug == 'userCompany') active  @endif"> <span class="d-none d-sm-inline">Empresas</span></a>
                </li>
                <li>
                    <a href="{{ route('indexEmployee') }}" class="nav-link @if($pageSlug == 'userEmployee') active  @endif"> <span class="d-none d-sm-inline">Enpleados</span></a>
                </li>
                <li>
                  <a href="{{ route('indexClient') }}" class="nav-link @if($pageSlug == 'userClient') active  @endif"> <span class="d-none d-sm-inline">Clientes</span></a>
              </li>
            </ul>
          </li>
        @endif
        <li class="nav-item">
          <a class="nav-link @if($pageSlug == 'profile') active  @endif" href="{{ route('indexProfile') }}">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fa-regular fa-address-card text-sm opacity-10" style=" color:rgb(0, 55, 128) !important;"></i>
            </div>
            <span class="nav-link-text ms-1">Perfil</span>
          </a>
        </li>
      </ul>     
    </div>
</aside>

