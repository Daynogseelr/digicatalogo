@extends('app', ['page' => __('Servicios'), 'pageSlug' => 'servicePDF'])

@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-1">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <h4 class="card-title">{{__('Service PDF Reports')}}</h4>
                    </div>
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="technician_filter">{{__('Tecnicos')}}:</label>
                                    <select class="form-control" id="technician_filter">
                                        <option value="TODOS">{{__('Todos los Tecnicos')}}</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date_filter">{{__('From Date')}}:</label>
                                    <input type="date" class="form-control" id="start_date_filter">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date_filter">{{__('To Date')}}:</label>
                                    <input type="date" class="form-control" id="end_date_filter">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button id="apply_filters" class="btn btn-primary">{{__('Apply Filters')}}</button>
                                <button id="clear_filters" class="btn btn-success">{{__('Clear Filters')}}</button>
                                <button id="generate_pdf" class="btn btn-info">{{__('Generate PDF')}}</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 12px;">
                                <table class="table table-striped" id="ajax-crud-datatableServicePDF" style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th>{{__('ID Service')}}</th>
                                            <th>{{__('Code')}}</th>
                                            <th>{{__('Date')}}</th>
                                            <th>{{__('Client')}}</th>
                                            <th>{{__('Technician')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Price')}} ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody >
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" style="text-align:right">{{__('Grand Total')}}:</th>
                                            <th id="grand_total_display">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
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
            let dataTable = $('#ajax-crud-datatableServicePDF').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.services.ajax') }}",
                    data: function (d) {
                        d.start_date_filter = $('#start_date_filter').val();
                        d.end_date_filter = $('#end_date_filter').val();
                        d.technician_filter = $('#technician_filter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 's.id' },
                    { data: 'code', name: 's.code' },
                    { data: 'formatted_date', name: 's.created_at' },
                    { data: 'client_name', name: 'c.name' },
                    { data: 'technician_name', name: 't.name' },
                    { data: 'status', name: 's.status' },
                    { data: 'price_formatted', name: 's.price', orderable: false, searchable: false } // Formatted for display
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    let api = this.api();
                    let total = api.column(6, {page:'current'}).data().reduce(function (a, b) {
                        // Directly use parseFloat from 'price_raw' which will be in the data
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    $('#grand_total_display').html(total.toFixed(2));
                },
                lengthMenu: [
                    [20, 30, 40, 50, -1],
                    ['20', '30', '40', '50', 'Todos']
                ],
                pageLength: 20,
                language: { // Using 'language' instead of 'oLanguage' for DataTables 1.10+
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} _MENU_ {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} _START_ {{__('to the')}} _END_ {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
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
                        "sSortAscending": ": {{__('Activate to sort the column in ascending order')}}",
                        "sSortDescending": ": {{__('Activate to sort the column in descending order')}}"
                    }
                }
            });

            $('#apply_filters').click(function() {
                dataTable.draw();
            });

            $('#clear_filters').click(function() {
                $('#start_date_filter').val('');
                $('#end_date_filter').val('');
                $('#technician_filter').val('TODOS');
                dataTable.draw();
            });

            $('#generate_pdf').click(function() {
                let startDate = $('#start_date_filter').val();
                let endDate = $('#end_date_filter').val();
                let technicianId = $('#technician_filter').val();

                // Get current DataTable search, order column, and direction
                let search = dataTable.search();
                let order = dataTable.order();
                let orderColumnIndex = order.length > 0 ? order[0][0] : null;
                let orderDir = order.length > 0 ? order[0][1] : null;

                let url = "{{ route('reports.services.pdf') }}" +
                    "?start_date=" + startDate +
                    "&end_date=" + endDate +
                    "&technician=" + technicianId +
                    "&search=" + search +
                    "&order_column=" + orderColumnIndex +
                    "&order_dir=" + orderDir;

                window.open(url, '_blank');
            });
        });
    </script>
@endsection