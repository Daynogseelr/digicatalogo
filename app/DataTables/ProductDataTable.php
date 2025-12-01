<?php
namespace App\DataTables;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Currency;
use Illuminate\Http\Request;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;

class ProductDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        $request = request();
        $currencyId = $request->get('currency_id');
        $inventoryId = $request->get('inventory_id');
        $stockFilter = $request->get('stock_filter', 'all');

        // Obtener tasa de cambio
        $currency = Currency::find($currencyId);

        return (new EloquentDataTable($query))
            ->addColumn('price', function ($row) use ($currency) {
                return number_format(($row->price * $currency->rate2) / $currency->rate, 2);
            })
            ->addColumn('price_currency', function ($row) use ($currency) {
                return number_format($row->price * $currency->rate2, 2);
            })
            ->addColumn('stock', function ($row) use ($inventoryId) {
                $stock = Stock::where('id_product', $row->id)
                    ->where('id_inventory', $inventoryId)
                    ->latest()
                    ->first();
                return $stock ? $stock->quantity : 0;
            })
            ->addColumn('images', function ($row) {
                $imgs = [];
                for ($i = 1; $i <= 3; $i++) {
                    $url = $row->{'url'.$i};
                    if ($url) {
                        $imgs[] = '<img src="' . asset('storage/' . $url) . '" onerror="this.onerror=null;this.src=\'' . asset('storage/products/product.png') . '\'" style="width:40px;height:40px;border-radius:6px;margin-right:4px;" />';
                    }
                }
                return implode('', $imgs);
            })
            ->addColumn('action', 'products.product-action')
            ->filter(function ($query) use ($stockFilter, $inventoryId) {
                if ($stockFilter == 'min') {
                    $query->whereIn('id', function($sub) use ($inventoryId) {
                        $sub->select('id_product')
                            ->from('stocks')
                            ->where('id_inventory', $inventoryId)
                            ->whereRaw('
                                quantity <= (
                                    SELECT stock_min FROM products WHERE products.id = stocks.id_product
                                )
                            ')
                            ->whereRaw('id = (
                                SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product AND s2.id_inventory = stocks.id_inventory
                            )');
                    });
                } elseif ($stockFilter == 'max') {
                    $query->whereIn('id', function($sub) use ($inventoryId) {
                        $sub->select('id_product')
                            ->from('stocks')
                            ->where('id_inventory', $inventoryId)
                            ->whereRaw('
                                quantity > (
                                    SELECT stock_min FROM products WHERE products.id = stocks.id_product
                                )
                            ')
                            ->whereRaw('id = (
                                SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product AND s2.id_inventory = stocks.id_inventory
                            )');
                    });
                }
            })
            ->rawColumns(['images', 'action']);
    }

    public function query(Product $model)
    {
        return $model->newQuery();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('products-table')
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
             ->buttons([
                ['extend' => 'copy', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'excel', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'csv', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'pdf', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'print', 'exportOptions' => ['columns' => ':not(.not-export)']],
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
    public function getColumns(): array
    {
        return [
            Column::make('code')->title('Código')->addClass('text-center'),
            Column::make('name')->title('Nombre'),
            Column::make('price')->title('Precio Base')->addClass('text-center'),
            Column::computed('price_currency')->title('Precio Moneda')->addClass('text-center'),
            Column::computed('stock')->title('Stock')->addClass('text-center'),
            Column::computed('images')->title('Imágenes')->addClass('text-center not-export')->orderable(false)->searchable(false)->exportable(false)->printable(false), 
            Column::computed('action')->title('Acciones')->addClass('text-center not-export')->orderable(false)->searchable(false)->exportable(false)->printable(false), 
        ];
    }

    protected function filename(): string
    {
        return 'Products_' . date('YmdHis');
    }
}