<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Ventas</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <link rel="apple-touch-icon" sizes="76x76" href="{{asset('assets/img/apple-icon.png')}}">
        <link rel="icon" type="image/png" href="{{asset('assets/img/favicon.png')}}">
        <!-- Nucleo Icons -->
        <link href="{{asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
        <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
        <!-- Font Awesome Icons -->
        <link  href="{{asset('fontawesome-free-6.5.2-web/css/all.min.css')}}" rel="stylesheet">
        <script src="{{asset('assets/js/fontawesome.js')}}" crossorigin="anonymous"></script>
        <link href="{{asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
        <!-- CSS Files -->
        <link id="pagestyle" href="{{asset('assets/css/argon-dashboard.css?v=2.0.4')}}" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}" />
        <link  href="{{asset('assets/css/jquery.dataTables.min.css')}}" rel="stylesheet">
        <!-- select2 -->
        <link rel="stylesheet" href="{{asset('assets/css/select2.min.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/select2-bootstrap-5-theme.min.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/select2-bootstrap-5-theme.rtl.min.css')}}" />
         
       
        @yield('css')  
    </head>

    <body  class="g-sidenav-show bg-gray-100 {{ $class ?? '' }}">
        @if ($pageSlug == 'login' || $pageSlug == 'register')
            @yield('content')  
        @else
            <div class="min-height-250 bg-primary position-absolute w-100"></div>
            @include('navbars.nav')
            <main class="main-content position-relative border-radius-lg ">
                @include('navbars.barra')
                @yield('content')
            </main>
        @endif 
       
         <!--   Core JS Files   -->
         <script src="{{asset('assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
         <script src="{{asset('assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
         <script src="{{asset('assets/js/plugins/chartjs.min.js')}}"></script>
         <script src="{{asset('assets/js/jquery.min.js')}}"></script>
         <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
         <script src="{{asset('assets/js/sweetalert2.js')}}"></script>
         <script>
             var win = navigator.platform.indexOf('Win') > -1;
             if (win && document.querySelector('#sidenav-scrollbar')) {
             var options = {
                 damping: '0.5'
             }
             Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
             }
         </script>
         <!-- Github buttons -->
         <script async defer src="https://buttons.github.io/buttons.js"></script>
         <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
         <script src="{{asset('assets/js/argon-dashboard.min.js?v=2.0.4')}}"></script>
         <!-- select2 -->
         <script src="{{asset('assets/js/core/bootstrap.bundle.min.js')}}"></script>
         <script src="{{asset('assets/js/select2.min.js')}}"></script>  
         
        <script>
            function validaMonto(evt){
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if (charCode != 46 && charCode != 44 && (charCode < 48 || charCode > 57)){
                    return false;
                }
                return true;
            }
            function mayus(e) {
                e.value = e.value.toUpperCase();
            }
            function validaNumericos(event) {
                if (event.charCode >= 48 && event.charCode <= 57) {
                    return true;
                }
                return false;
            }
            function sololetras(evento) {
                key = evento.keyCode || evento.which;
                teclado = String.fromCharCode(key).toLocaleLowerCase();
                letras = " áéíóúabcdefghijklmnñopqrstuvwxyz";
                especiales = "8-32-37-38-39-46-164";

                teclado_especial = false;
                for (var i in especiales) {
                    if (key == especiales[i]) {
                        teclado_especial = true;
                        break;
                    }
                }
                if (letras.indexOf(teclado) == -1 && !teclado_especial) {
                    return false;
                }
            }
        </script>
        @yield('scripts')
        @yield('js')
    </body>
</html>