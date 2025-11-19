<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class ServiceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('action', 'services.service-action')
            ->filterColumn('client', function($query, $keyword) {
                $query->whereRaw("LOWER(TRIM(CONCAT(client.name, ' ', COALESCE(client.last_name, '')))) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('technician', function($query, $keyword) {
                $query->whereRaw("LOWER(TRIM(CONCAT(technician.name, ' ', COALESCE(technician.last_name, '')))) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('ci', function($query, $keyword) {
                $query->whereRaw("LOWER(CONCAT(client.nationality, '-', client.ci)) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->rawColumns(['action']);
    }

    public function query()
    {
        $status = request('status');
        $query = DB::table('services')
            ->join('users as client', 'client.id','=','services.id_client')
            ->leftJoin('users as technician', 'technician.id','=','services.id_technician')
            ->join('service_categories as category', 'category.id','=','services.id_category')
            ->select(
                'client.phone as phone',
                'services.id_client as id_client',
                'services.id_technician as id_technician',
                'services.id',
                'services.created_at',
                'services.ticker',
                DB::raw("TRIM(CONCAT(client.name, ' ', COALESCE(client.last_name, ''))) as client"),
                DB::raw("CONCAT(client.nationality, '-', client.ci) as ci"),
                'category.name as category',
                DB::raw("TRIM(CONCAT(technician.name, ' ', COALESCE(technician.last_name, ''))) as technician"),
                'services.status'
            )
            ->orderBy('services.created_at', 'desc');

        if ($status) {
            $query->where('services.status', $status);
        }

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('services-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
            ->pageLength(20) // <-- Muestra 20 por defecto
            ->orderBy(0)
            ->responsive(true)
            ->language([
                'url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            ])
            ->initComplete($this->getInitCompleteScript());
    }

    protected function getInitCompleteScript(): string
    {
        return <<<JS
function () {
    var api = this.api();
    var thead = $(api.table().header());
    var filterRow = $('<tr>').addClass('filters').appendTo(thead);
    api.columns().every(function (index) {
        var newTh = $('<th>').appendTo(filterRow);
        if (index === api.columns().count() - 1) {
            return;
        }
        var column = this;
        var title = thead.find('th').eq(index).text();
        var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar ' + title + '" />');
        input.appendTo(newTh);
        input.on('click', function (e) { e.stopPropagation(); });
        input.on('keyup change clear', function () {
            if (column.search() !== this.value) {
                column.search(this.value).draw();
            }
        });
    });
    function updateFilterVisibility() {
        var mainThs = thead.find('tr').first().find('th');
        filterRow.find('th').each(function (i) {
            if (mainThs.eq(i).css('display') === 'none') {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }
    updateFilterVisibility();
    api.on('responsive-resize responsive-display draw', function () {
        updateFilterVisibility();
    });
    setTimeout(updateFilterVisibility, 200);
}
JS;
    }

    protected function getColumns()
    {
        return [
            Column::make('created_at')->title('Fecha')->addClass('all'),
            Column::make('ticker')->title('Ticket')->addClass('all'),
            Column::make('client')->title('Cliente')->addClass('all'),
            Column::make('ci')->title('CI')->addClass('all'),
            Column::make('category')->title('Categoría')->addClass('all'),
            Column::make('technician')->title('Técnico')->addClass('all'),
            Column::make('status')->title('Estado')->addClass('all'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center not-export')
                ->searchable(false)
                ->orderable(false)
                ->title('Acciones')
                ->addClass('all'),
        ];
    }

    protected function filename(): string
    {
        return 'Services_' . date('YmdHis');
    }
}