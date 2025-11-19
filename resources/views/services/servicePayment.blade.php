
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'servicePayment'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row">
            <div class="col-lg-3 mb-lg-0 ">
                <div class="card z-index-2 h-100" style="height: 400px !important;">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 100% !important;">
                                <h4>{{__('Producción')}}</h4>
                            </div> 
                        </div>
                    </div>     
                    <div class="card-body"> 
                        <div class="card-body"> 
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id=""
                                    style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Description')}}</th>
                                            <th class="text-center">{{__('Amount')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{__('Produccion Total')}}</td>
                                            <td class="text-end">{{ $production->total_price_current_month }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{__('Casos Atendidos')}}</td>
                                            <td class="text-end">{{ $countService}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-lg-0 ">
                <div class="card z-index-2 h-100" style="height: 400px !important;">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 100% !important;">
                                <h4>{{__('Comisiones')}}</h4>
                            </div> 
                        </div>
                    </div>     
                    <div class="card-body p-3"> 
                        <div class="card-body"> 
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id=""
                                    style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Technician')}}</th>
                                            <th class="text-center">{{__('Percent')}}</th>
                                            <th class="text-center">{{__('Amount')}}</th>
                                            <th class="text-center">{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (auth()->user()->type == 'EMPRESA')
                                            @foreach ($payments as $payment)
                                            @php
                                                $amount = $payment->amount;
                                            @endphp
                                                <tr>
                                                    <td>{{ $payment->technicianName }} {{ $payment->technicianLast_name }}</td>
                                                    <td class="text-center">{{ $payment->percent }}</td> 
                                                    <td class="text-end">{{ number_format($amount, 2) }}</td>
                                                    <td class="text-center">
                                                        <div class="row text-center"> 
                                                            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                                                                <a style="margin: -6px !important; padding: 5px;" href="javascript:void(0)" data-toggle="tooltip" onClick="modalServicePercent({{ $payment->id_service_payment }})"  data-original-title="Edit" class="edit btn btn-primary "> 
                                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                                </a>
                                                            </div> 
                                                            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                                                                <a style="margin: -6px !important; padding: 5px;" href="javascript:void(0)" data-toggle="tooltip" onClick="modalServicePayment({{ $payment->id_service_payment }})"  data-original-title="Edit" class="edit btn btn-info "> 
                                                                    <i class="fa-regular fa-eye"  style="color: White  !important;"></i>
                                                                </a>
                                                            </div> 
                                                            <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4" style="padding:0;">  
                                                                <a style="margin: -6px !important; padding: 5px;" href="{{ route('servicePaymenCommission', ['id' => $payment->id_service_payment]) }}" data-toggle="tooltip"  data-original-title="Edit" class="edit btn btn-success "> 
                                                                    <i class="fa-solid fa-money-bill"></i> 
                                                                </a>
                                                            </div> 
                                                        </div> 
                                                    </td>
                                                </tr>
                                            @endforeach     
                                        @else
                                            @foreach ($payments as $payment)
                                                @php
                                                    $amount = $payment->amount;
                                                @endphp
                                                @if (auth()->id() ==  $payment->id)
                                                    <tr>
                                                        <td>{{ $payment->technicianName }} {{ $payment->technicianLast_name }}</td>
                                                        <td class="text-center">{{ $payment->percent }}</td> 
                                                        <td class="text-end">{{ number_format($amount, 2) }}</td>
                                                        <td class="text-center">
                                                            <div class="row text-center"> 
                                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="padding:0;">  
                                                                    <a style="margin: -6px !important; padding: 5px;" href="javascript:void(0)" data-toggle="tooltip" onClick="modalServicePayment({{ $payment->id_service_payment }})"  data-original-title="Edit" class="edit btn btn-info "> 
                                                                        <i class="fa-regular fa-eye"  style="color: White  !important;"></i>
                                                                    </a>
                                                                </div> 
                                                            </div> 
                                                        </td>
                                                    </tr>      
                                                @endif 
                                            @endforeach         
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <div class="text-center">
                                @if (auth()->user()->type == 'EMPRESA')
                                    <form action="{{ route('servicePaymenCommissionAll') }}" method="POST">
                                        @csrf {{-- Token CSRF obligatorio en Laravel --}}
                                        <button type="submit" class="btn btn-primary">Pagar Comisiones</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-lg-0 ">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">  
                                <h4>{{__('Estadistica')}}</h4>   
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="serviciosDiariosMes" style="height: 350px; min-width: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-lg-0 mb-4 mt-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-12">
                                        <h4>{{__('Service Payment')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id="ajax-crud-datatableServicePayment" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}} Inicio</th>
                                            <th class="text-center">{{__('Date')}} Final</th>
                                            <th class="text-center">{{__('Technician')}}</th>
                                            <th class="text-center">{{__('Percent')}}</th>
                                            <th class="text-center">{{__('Amount')}} ($)</th>
                                            <th class="text-center">{{__('Status')}}</th>
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
    <!-- boostrap servicePayment model -->
    <div class="modal fade" id="servicePayment-modal" aria-hidden="true" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Service Payment')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tabla table-responsive" >    
                        <br> 
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Client')}}</th>
                                    <th>{{__('Solution')}}</th>
                                    <th>{{__('Amount')}}</th>
                                    <th>{{__('Comision')}}</th>
                                </tr>
                            </thead>
                            <tbody id="dtmodal">

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap servicePercent model -->
    <div class="modal fade" id="servicePercent-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Cambiar Porcentaje')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form method="post" action="{{ route('modalServicePercent') }}" autocomplete="off">
                            @csrf
                            @method('post')
                            <input type="hidden" name="id" id="id">
                            <div class="col-md-12 col-sm-12 form-outline mb-2 {{ $errors->has('percent') ? ' has-danger' : '' }}">
                                <input name="percent" type="text" class="form-control{{ $errors->has('percent') ? ' is-invalid' : '' }}" id="percent"
                                    placeholder="{{__('Porcentaje de comision')}}" title="Es obligatorio un descuento" minlength="1"
                                    maxlength="10" required onkeypress='return validaMonto(event)'>
                                <label class="form-label" for="form2Example17">{{__('Porcentaje de comision')}}</label>
                                @include('alerts.feedback', ['field' => 'percent'])
                            </div>
                            <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                                <button type="submit" class="btn btn-primary" id="btn-save">{{__('Send')}}</button>
                            </div>
                        </form>
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
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
            $('#ajax-crud-datatableServicePayment').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableServicePayment') }}",
                columns: [
                    { data: 'dateStart', name: 'dateStart' },
                    { data: 'dateEnd', name: 'dateEnd' },
                    {
                        data: 'technicianName',
                        render: function(data, type, row) {
                            if (row.technicianName == null) {
                                return 'Sin asignar';
                            } else {
                                return `${row.technicianName} ${row.technicianLast_name}`;
                            }
                        }
                    },
                    { data: 'percent', name: 'percent' },
                    { data: 'amount', name: 'amount' },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            if (data == 0) {
                                return 'PENDIENTE';
                            } else if (data == 1) {
                                return 'PAGADO';
                            } else {
                                return data; // Handle other cases if needed
                            }
                        }
                    },
                    { data: 'action', name: 'action', orderable: false},
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="10">10</option>' +
                        '<option value="20">20</option>' +
                        '<option value="30">30</option>' +
                        '<option value="40">40</option>' +
                        '<option value="50">50</option>' +
                        "<option value='-1'>{{__('All')}}</option>" +
                        "</select> {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} (_START_ {{__('to the')}} _END_) {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
                    "sInfoEmpty": "{{__('Showing 0 to 0 of a total of 0 records')}}",
                    "sInfoFiltered": "({{__('of')}} _MAX_ {{__('existents')}})",
                    "sInfoPostFix": "",
                    "sSearch": "{{__('Search')}}:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "{{__('Please wait - loading')}}...",
                    "oPaginate": {
                        "sFirst": "{{__('First')}}",
                        "sLast": "{{__('Last')}}",
                        "sNext": "{{__('Next')}}",
                        "sPrevious": "{{__('Previous')}}"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
            function centerTableContent() {
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(3)').addClass('text-center');
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(5)').addClass('text-center');
                $('#ajax-crud-datatableServicePayment tbody tr td:nth-child(6)').addClass('text-center');
            }
            if (document.getElementById("serviciosDiariosMes")) {
    $.ajax({
        url: '{{ route('chartServiceMonth') }}',
        type: 'GET',
        async: true,
        success: function (response) {
            Highcharts.chart('serviciosDiariosMes', {
                chart: {
                    type: 'column',
                    marginLeft: 60,
                    marginRight: 20,
                    marginTop: 30,
                    marginBottom: 70,
                    options3d: {
                        enabled: true,
                        alpha: 10,
                        beta: 15,
                        viewDistance: 25,
                        depth: 40
                    },
                    style: {
                        fontFamily: 'Inter, sans-serif'
                    }
                },
                title: { text: null },
                subtitle: {
                    text: '{{__("Servicios entregados diarios del mes")}}',
                    align: 'center',
                    style: { fontSize: '14px', color: '#67748e' }
                },
                xAxis: {
                    categories: response.map(item => item.day),
                    labels: {
                        rotation: -45,
                        style: { fontSize: '11px', color: '#67748e' }
                    },
                    lineColor: '#ddd',
                    tickColor: '#ddd'
                },
                yAxis: [{
                    allowDecimals: false,
                    min: 0,
                    title: {
                        text: "{{__('Cantidad')}}",
                        style: { fontSize: '14px', fontWeight: 'bold', color: '#344767' }
                    },
                    labels: { style: { color: '#67748e' } },
                    gridLineColor: '#eee'
                }, {
                    title: {
                        text: "{{__('Total')}} ($)",
                        style: { fontSize: '14px', fontWeight: 'bold', color: '#344767' }
                    },
                    labels: { style: { color: '#67748e' } },
                    gridLineColor: '#eee',
                    opposite: true
                }],
                tooltip: {
                    shared: true,
                    useHTML: true,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#ccc',
                    borderWidth: 1,
                    formatter: function () {
                        let idx = this.points[0].point.index;
                        let cantidad = response[idx].cantidad;
                        let total = response[idx].total;
                        return `<b>${response[idx].day}</b><br>
                                {{__('Cantidad')}}: <b>${cantidad}</b><br>
                                {{__('Total')}}: <b>$${total.toFixed(2)}</b>`;
                    }
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        depth: 40,
                        borderRadius: 3,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}',
                            style: { fontWeight: 'bold', textOutline: 'none', color: 'Contrast' }
                        },
                        pointPadding: 0.1,
                        groupPadding: 0.2
                    }
                },
                series: [
                    {
                        name: "{{__('Cantidad')}}",
                        data: response.map(item => item.cantidad),
                        color: '#fbc687',
                        yAxis: 0
                    },
                    {
                        name: "{{__('Total')}} ($)",
                        data: response.map(item => item.total),
                        color: '#5e72e4',
                        yAxis: 1,
                        type: 'spline',
                        marker: { enabled: true }
                    }
                ],
                legend: { enabled: true },
                credits: { enabled: false }
            });
        },
        error: function (error) {
            $('#serviciosDiariosMes').html('<div class="alert alert-danger text-center">{{__("Error cargando la gráfica. Intente de nuevo.")}}</div>');
        }
    });
}
        });     
        function modalServicePayment(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('modalServicePayment') }}",
                data:{ 
                    id: id,
                },
                dataType: 'json',
                success: function(res) {
                    $('#servicePayment-modal').modal('show');
                    console.log(res);
                    $('#dtmodal').html('');  
                    
                    $.each(res.servicePayments, function(index, elemento){
                        if (elemento.status == 1) {
                            $('#dtmodal').append(  
                                '<tr style=" font-size: 12px;">'+
                                    '<td>'+elemento.created_at+'</td>'+
                                    '<td>'+elemento.name+' '+elemento.last_name+'</td>'+
                                    '<td style=" text-align: center;">'+elemento.procedure+'</td>'+
                                    '<td style=" text-align: end;">'+elemento.price+'</td>'+
                                    '<td style=" text-align: end;">'+elemento.commission_amount+'</td>'+
                                '</tr>'              
                            );  
                        } else {
                            var price = parseFloat(elemento.commission_amount);
                            $('#dtmodal').append(  
                                '<tr style=" font-size: 12px;">'+
                                    '<td>'+elemento.created_at+'</td>'+
                                    '<td>'+elemento.name+' '+elemento.last_name+'</td>'+
                                    '<td style=" text-align: center;">'+elemento.procedure+'</td>'+
                                    '<td style=" text-align: end;">'+elemento.price+'</td>'+
                                    '<td style=" text-align: end;">'+price.toFixed(2)+'</td>'+
                                '</tr>'              
                            );  
                        }
                        
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
        function modalServicePercent(id) {
            $('#servicePercent-modal').modal('show');
            $('#id').val(id);
        }
    </script>
@endsection

