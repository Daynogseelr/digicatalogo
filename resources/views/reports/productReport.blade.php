
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'productReport'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-4">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-12">
                                        <h4>{{__('Reporte Graficas Productos')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="row text-center"> 
                            <div class="col-sm-1"></div>
                            <div class="col-sm-2 ">
                                <div class="form-group">
                                    <label for="form" class="col-form-label">Forma :</label>
                                    <select name="form" class="form-select" id="form">
                                        <option selected value="quantity">Cantidad</option>
                                        <option value="total">Total</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="type" class="col-form-label">Categoria :</label>
                                    <select name="type" class="form-select type">
                                        <option value="">Selecionar</option>
                                        <option value="todos">Todas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="desde" class="col-form-label">Desde :</label>
                                    <select name="desde" class="form-select desde">
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="hasta" class="col-form-label">Hasta :</label>
                                    <select name="hasta" class="form-select hasta">
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="desdeM" class="col-form-label">Mes :</label>
                                    <select name="desdeM" class="form-select desdeM">
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-1"></div>
                            <div class="col-sm-12" id="grafi">
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
        if (document.getElementById("grafi")) {
            const action = "index";
            $.ajax({
                url: '{{ route('reporte.product') }}',
                type: 'GET',
                data: {
                    'action':action
                },
                async: true,
                success: function (response) {
                    Highcharts.chart('grafi', {
                        chart: {
                            type: 'column',
                            options3d: {
                                enabled: true,
                                alpha: 15,
                                beta: 15,
                                viewDistance: 25,
                                depth: 40
                            }
                        },
                        title: {
                            text: '',
                            align: 'left'
                        },
                        xAxis: {
                            categories: ['Productos'],
                            labels: {
                                skew3d: true,
                                style: {
                                    fontSize: '16px'
                                }
                            }
                        },
                        yAxis: {
                            allowDecimals: false,
                            min: 0,
                            title: {
                                text: 'Cantidad de Productos',
                                skew3d: true,
                                style: {
                                    fontSize: '16px'
                                }
                            }
                        },
                        tooltip: {
                            headerFormat: '<b>{point.key}</b><br>',
                            pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: {point.y} / {point.stackTotal}'
                        },
                        plotOptions: {
                            column: {
                                stacking: 'normal',
                                depth: 40
                            }
                        },
                        series:  [{
                            name: "{{__('Products')}}",
                            data: [response.puntosB],
                            stack: 'male'
                            },{
                            name: "{{__('Cart')}}",
                            data: [response.puntosC],
                            stack: 'fale'
                            }
                        ]
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
        $(document).on('change', 'select[name=form]', function(event){
            var form = $('select[name=form]').val();
            const action = "form";
            $('.desde').html('');
            $('.hasta').html('');
            $('.desdeM').html('');
            $.ajax({
                url: '{{ route('reporte.product') }}',
                type: 'GET',
                data: {
                    'form':form,
                    'action':action
                },
                async: true,
                success: function (response) {
                    if (response.form == 'Cantidad') {
                        Highcharts.chart('grafi', {
                            chart: {
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                }
                            },
                            title: {
                                text: '',
                                align: 'left'
                            },
                            xAxis: {
                                categories: ['Productos'],
                                labels: {
                                    skew3d: true,
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            },
                            yAxis: {
                                allowDecimals: false,
                                min: 0,
                                title: {
                                    text: 'Cantidad de Productos',
                                    skew3d: true,
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: {point.y} / {point.stackTotal}'
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40
                                }
                            },
                            series:  [{
                                name: "{{__('Products')}}",
                                data: [response.puntosB],
                                stack: 'male'
                                },{
                                name: "{{__('Cart')}}",
                                data: [response.puntosC],
                                stack: 'fale'
                                }
                            ]
                        });
                    } else {
                        Highcharts.chart('grafi', {
                            chart: {
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                }
                            },
                            title: {
                                text: '',
                                align: 'left'
                            },
                            xAxis: {
                                categories: ['Productos'],
                                labels: {
                                    skew3d: true,
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            },
                            yAxis: {
                                allowDecimals: false,
                                min: 0,
                                title: {
                                    text: 'Cantidad de Productos',
                                    skew3d: true,
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: {point.y} / {point.stackTotal}'
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40
                                }
                            },
                            series:  [{
                                name: "{{__('Products')}}",
                                data: [response.puntosB],
                                stack: 'male'
                                },{
                                name: "{{__('Cart')}}",
                                data: [response.puntosC],
                                stack: 'fale'
                                }
                            ]
                        });
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
    </script>
@endsection
