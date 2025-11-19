<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Employee;
use App\Models\User;
use App\Models\Bill;
use App\Models\Bill_payment;
use App\Models\Stock;   // Asegúrate de importar el modelo Stock
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportPDFController extends Controller
{
     public function indexProductPDF()
    {
        $inventories = Inventory::get();
        return view('reports.pdfs.product', compact('inventories'));
    }

    public function ajaxProductPDF(Request $request)
    {
      
        $stockFilter = $request->input('stock_filter');
        $inventoryFilter = $request->input('inventory_filter');
        $searchValue = $request->input('search.value');

        // Subconsulta para obtener el último stock de cada producto en cada inventario
        $latestStocksSubquery = DB::table('stocks as s2')
            ->select('s2.id_product', 's2.id_inventory', DB::raw('MAX(s2.created_at) as max_created_at'))
            ->groupBy('s2.id_product', 's2.id_inventory');

        $productsQuery = Product::from('products as p')
            ->leftJoinSub($latestStocksSubquery, 'latest_s', function($join) {
                $join->on('p.id', '=', 'latest_s.id_product');
            })
            ->leftJoin('stocks as s', function($join) {
                $join->on('s.id_product', '=', 'latest_s.id_product')
                     ->on('s.id_inventory', '=', 'latest_s.id_inventory')
                     ->on('s.created_at', '=', 'latest_s.max_created_at');
            })
            ->leftJoin('inventories as i', 's.id_inventory', '=', 'i.id')
            ->select(
                'p.id',
                'p.name',
                'p.price',
                's.created_at as latest_stock_date', // El created_at del último stock
                's.quantity as current_stock_quantity', // La cantidad del último stock
                'p.code',
                'i.name as inventory_name',
                'p.created_at' // Select the product's created_at for ordering if needed
            )
            ->orderBy('p.created_at', 'desc'); // **CORRECCIÓN AQUÍ: Usar 'p.created_at'**

        // Apply inventory filter
        if ($inventoryFilter && $inventoryFilter !== 'TODOS_INVENTARIOS') {
            $productsQuery->where('s.id_inventory', $inventoryFilter);
        }

        // Apply stock filter
        if ($stockFilter === 'SINSTOCK') {
            $productsQuery->where('s.quantity', '=', 0);
        } elseif ($stockFilter === 'NEGATIVOS') {
            $productsQuery->where('s.quantity', '<', 0);
        } elseif ($stockFilter === 'MAYORACERO') {
            $productsQuery->where('s.quantity', '>', 0);
        }
        // If 'TODOS', no stock filter is applied here.

        // Custom search filter for DataTables (server-side)
        if (!empty($searchValue)) {
            $productsQuery->where(function($query) use ($searchValue) {
                $query->where('p.name', 'like', '%' . $searchValue . '%')
                      ->orWhere('p.code', 'like', '%' . $searchValue . '%')
                      ->orWhere('i.name', 'like', '%' . $searchValue . '%');
            });
        }

        return DataTables::of($productsQuery)
            ->addColumn('formatted_created_at', function ($product) {
                return $product->latest_stock_date ? (new Carbon($product->latest_stock_date))->format('d/m/Y H:i:s') : '';
            })
            ->addColumn('quantity', function ($product) {
                return $product->current_stock_quantity ?? 0;
            })
            ->addColumn('total', function ($product) {
                $quantity = $product->current_stock_quantity ?? 0;
                $total = $quantity * $product->price;
                return number_format($total, 2, '.', '');
            })
            ->rawColumns(['total'])
            ->make(true);
    }


    public function pdfProduct(Request $request)
    {

        $stockFilter = $request->input('type');
        $inventoryFilter = $request->input('inventory');
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        // Subconsulta para obtener el último stock de cada producto en cada inventario
        $latestStocksSubquery = DB::table('stocks as s2')
            ->select('s2.id_product', 's2.id_inventory', DB::raw('MAX(s2.created_at) as max_created_at'))
            ->groupBy('s2.id_product', 's2.id_inventory');

        $productsQuery = Product::from('products as p')
        ->leftJoinSub($latestStocksSubquery, 'latest_s', function($join) {
            $join->on('p.id', '=', 'latest_s.id_product');
        })
        ->leftJoin('stocks as s', function($join) {
            $join->on('s.id_product', '=', 'latest_s.id_product')
                 ->on('s.id_inventory', '=', 'latest_s.id_inventory')
                 ->on('s.created_at', '=', 'latest_s.max_created_at');
        })
        ->leftJoin('inventories as i', 's.id_inventory', '=', 'i.id')
        ->select(
            'p.id',
            'p.name',
            'p.price', // <--- Make sure price is selected
            's.created_at as latest_stock_date',
            's.quantity as current_stock_quantity', // <--- Make sure quantity is selected with this alias
            'p.code',
            'i.name as inventory_name',
            'p.created_at'
        );

        // Apply inventory filter
        if ($inventoryFilter && $inventoryFilter !== 'TODOS_INVENTARIOS') {
            $productsQuery->where('s.id_inventory', $inventoryFilter);
        }

        // Apply stock filter
        if ($stockFilter === 'SINSTOCK') {
            $productsQuery->where('s.quantity', '=', 0);
        } elseif ($stockFilter === 'NEGATIVOS') {
            $productsQuery->where('s.quantity', '<', 0);
        } elseif ($stockFilter === 'MAYORACERO') {
            $productsQuery->where('s.quantity', '>', 0);
        }

        // Apply search query from DataTables to PDF query
        if (!empty($searchQuery)) {
            $productsQuery->where(function($query) use ($searchQuery) {
                $query->where('p.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('p.code', 'like', '%' . $searchQuery . '%')
                      ->orWhere('i.name', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply ordering from DataTables to PDF query
        // Mapeo de índices de columnas a nombres de columnas de base de datos/alias
        $columnNames = [
            0 => 's.created_at',      // 'Date' -> latest_stock_date
            1 => 'p.code',            // 'Code'
            2 => 'p.name',            // 'Name'
            3 => 'i.name',            // 'Inventory'
            4 => 'p.price',           // 'Price'
            5 => 's.quantity',        // 'current_stock_quantity'
            // 6 => 'total' - esta columna no es de la DB y no se puede ordenar directamente aquí
        ];

        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            $productsQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
        } else {
            // Default ordering if none specified from DataTable
            $productsQuery->orderBy('p.created_at', 'desc'); // **CORRECCIÓN AQUÍ: Usar 'p.created_at'**
        }


        $products = $productsQuery->get();

        $total = 0; // This is the GRAND TOTAL for the PDF
        foreach ($products as $product) {
            // Ensure that current_stock_quantity and price are available.
            // If current_stock_quantity is null (no stock record found), default to 0.
            // If price is null (shouldn't happen for products), default to 0.
            $quantity = $product->current_stock_quantity ?? 0;
            $price = $product->price ?? 0; // It's good practice to null-coalesce price too

            $product->individual_total = $quantity * $price; // Calculate individual total for PDF display
            $total += $product->individual_total; // Add to grand total
        }

        $stockFilterName = '';
        if ($stockFilter === 'TODOS') {
            $stockFilterName = __('Todos los Productos');
        } elseif ($stockFilter === 'SINSTOCK') {
            $stockFilterName = __('Productos con Stock en 0');
        } elseif ($stockFilter === 'NEGATIVOS') {
            $stockFilterName = __('Productos con Stock Negativo');
        } elseif ($stockFilter === 'MAYORACERO') {
            $stockFilterName = __('Productos con Stock Mayor a 0');
        }

        $inventoryName = __('Todos los Inventarios');
        if ($inventoryFilter && $inventoryFilter !== 'TODOS_INVENTARIOS') {
            $selectedInventory = Inventory::find($inventoryFilter);
            if ($selectedInventory) {
                $inventoryName = $selectedInventory->name;
            }
        }


        $pdf = PDF::loadView('pdf.product', compact('products', 'total', 'stockFilterName', 'inventoryName', 'searchQuery', 'orderColumnIndex', 'orderDirection'));
        return $pdf->stream('products_report.pdf');
    }
    public function indexBillPDF()
    {
         return view('reports.pdfs.bill');
 
    }

     public function ajaxBillPDF(Request $request)
    {

        $billTypeFilter = $request->input('bill_type_filter');
        $startDateFilter = $request->input('start_date_filter');
        $endDateFilter = $request->input('end_date_filter');
        // Removed $monthFilter = $request->input('month_filter');
        $searchValue = $request->input('search.value');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id')
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA'])
            ->select(
                'b.code',
                'b.created_at',
                'b.net_amount',
                'b.type',
                'c.name as client_name'
            );

        // Apply bill type filter
        if ($billTypeFilter && $billTypeFilter !== 'TODAS') {
            $billsQuery->where('b.type', $billTypeFilter);
        }

        // Apply date range filter
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Removed month filter
        // if ($monthFilter) {
        //     $billsQuery->whereMonth('b.created_at', $monthFilter);
        // }

        // Custom search filter for DataTables (server-side)
        if (!empty($searchValue)) {
            $billsQuery->where(function($query) use ($searchValue) {
                $query->where('b.code', 'like', '%' . $searchValue . '%')
                      ->orWhere('c.name', 'like', '%' . $searchValue . '%')
                      ->orWhere('b.type', 'like', '%' . $searchValue . '%');
            });
        }

        return DataTables::of($billsQuery)
            ->addColumn('formatted_date', function ($bill) {
                return (new Carbon($bill->created_at))->format('d/m/Y H:i:s');
            })
            // This is the column displayed in the table, formatted as a string
            ->addColumn('net_amount', function ($bill) {
                return number_format($bill->net_amount ?? 0, 2, '.', '');
            })
            // This is the raw number for footerCallback calculations, ensure it's named 'net_amount_raw'
            ->addColumn('net_amount_raw', function ($bill) {
                return $bill->net_amount ?? 0;
            })
            ->rawColumns(['net_amount']) // Use 'net_amount' if you want HTML in this column
            ->make(true);
    }

    public function pdfBill(Request $request)
    {

        $billTypeFilter = $request->input('type');
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        // Removed $monthFilter = $request->input('month');
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id')
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA'])
            ->select(
                'b.code',
                'b.created_at',
                'b.net_amount',
                'b.type',
                'c.name as client_name'
            );

        // Apply bill type filter
        if ($billTypeFilter && $billTypeFilter !== 'TODAS') {
            $billsQuery->where('b.type', $billTypeFilter);
        }

        // Apply date range filter
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Removed month filter
        // if ($monthFilter) {
        //     $billsQuery->whereMonth('b.created_at', $monthFilter);
        // }

        // Apply search query from DataTables to PDF query
        if (!empty($searchQuery)) {
            $billsQuery->where(function($query) use ($searchQuery) {
                $query->where('b.code', 'like', '%' . $searchQuery . '%')
                      ->orWhere('c.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('b.type', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply ordering from DataTables to PDF query
        $columnNames = [
            0 => 'b.code',
            1 => 'b.created_at',
            2 => 'c.name',
            3 => 'b.type',
            4 => 'b.net_amount',
        ];

        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            if ($orderByColumn === 'b.net_amount') {
                 $billsQuery->orderByRaw('CAST(b.net_amount AS DECIMAL(10, 2)) ' . ($orderDirection ?? 'asc'));
            } else {
                 $billsQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
            }
        } else {
            // Default ordering
            $billsQuery->orderBy('b.created_at', 'desc');
        }

        $bills = $billsQuery->get();

        $grandTotal = 0;
        foreach ($bills as $bill) {
            $grandTotal += $bill->net_amount ?? 0; // Ensure you're using net_amount here
        }

        // Prepare filter names for the PDF
        $billTypeFilterName = '';
        if ($billTypeFilter === 'TODAS') {
            $billTypeFilterName = 'Todas las Facturas';
        } elseif ($billTypeFilter === 'FACTURA') {
            $billTypeFilterName = 'Solo Facturas';
        } elseif ($billTypeFilter === 'CREDITO') {
            $billTypeFilterName = 'Solo Notas de Crédito';
        }

        $dateRangeFilterName = 'Todas las Fechas';
        if ($startDateFilter && $endDateFilter) {
            $dateRangeFilterName = Carbon::parse($startDateFilter)->format('d/m/Y') . ' - ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        } elseif ($startDateFilter) {
            $dateRangeFilterName = 'Desde ' . Carbon::parse($startDateFilter)->format('d/m/Y');
        } elseif ($endDateFilter) {
            $dateRangeFilterName = 'Hasta ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        }

        // Removed monthFilterName logic
        $monthFilterName = 'No Aplicado'; // Or simply remove this line if you don't want to show it in PDF

        $pdf = PDF::loadView('pdf.bills', compact('bills', 'grandTotal', 'billTypeFilterName', 'dateRangeFilterName', 'monthFilterName', 'searchQuery', 'orderColumnIndex', 'orderDirection'));
        return $pdf->stream('bills_report.pdf');
    }
    public function indexServicePDF()
    {
            $employeeIds = Employee::pluck('id_employee'); // Obtiene solo los id_employee
            // Obtener los usuarios que son estos empleados (los técnicos)
            $technicians = User::whereIn('id', $employeeIds)->get();
            return view('reports.pdfs.service', compact('technicians'));
    }

    public function ajaxServicePDF(Request $request)
    {
        $startDateFilter = $request->input('start_date_filter');
        $endDateFilter = $request->input('end_date_filter');
        $technicianFilter = $request->input('technician_filter');
        $searchValue = $request->input('search.value');

        $servicesQuery = Service::from('services as s')
            ->leftJoin('users as c', 's.id_client', '=', 'c.id') // Join for client name
            ->leftJoin('users as t', 's.id_technician', '=', 't.id') 
            ->select(
                's.id',
                's.code',
                's.ticker',
                's.model',
                's.brand',
                's.serial',
                's.description',
                's.solution',
                's.price', // Select price for individual display and sum
                's.status',
                's.created_at',
                'c.name as client_name',
                't.name as technician_name'
            );

        // Apply date range filter
        if ($startDateFilter) {
            $servicesQuery->whereDate('s.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $servicesQuery->whereDate('s.created_at', '<=', $endDateFilter);
        }

        // Apply technician filter
        if ($technicianFilter && $technicianFilter !== 'TODOS') {
            $servicesQuery->where('s.id_technician', $technicianFilter);
        }

        // Custom search filter for DataTables (server-side)
        if (!empty($searchValue)) {
            $servicesQuery->where(function($query) use ($searchValue) {
                $query->where('s.code', 'like', '%' . $searchValue . '%')
                      ->orWhere('s.ticker', 'like', '%' . $searchValue . '%')
                      ->orWhere('s.model', 'like', '%' . $searchValue . '%')
                      ->orWhere('s.brand', 'like', '%' . $searchValue . '%')
                      ->orWhere('c.name', 'like', '%' . $searchValue . '%')
                      ->orWhere('t.name', 'like', '%' . $searchValue . '%');
            });
        }

        return DataTables::of($servicesQuery)
            ->addColumn('formatted_date', function ($service) {
                return (new Carbon($service->created_at))->format('d/m/Y H:i:s');
            })
            ->addColumn('price_formatted', function ($service) {
                return number_format($service->price ?? 0, 2, '.', '');
            })
            // Raw price for calculations in footerCallback
            ->addColumn('price_raw', function ($service) {
                return $service->price ?? 0;
            })
            ->rawColumns(['price_formatted'])
            ->make(true);
    }

    public function pdfService(Request $request)
    {
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $technicianFilter = $request->input('technician'); // 'technician' from URL param
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        $servicesQuery = Service::from('services as s')
            ->leftJoin('users as c', 's.id_client', '=', 'c.id')
            ->leftJoin('users as t', 's.id_technician', '=', 't.id')
            ->select(
                's.id',
                's.code',
                's.ticker',
                's.model',
                's.brand',
                's.serial',
                's.description',
                's.solution',
                's.price',
                's.status',
                's.created_at',
                'c.name as client_name',
                't.name as technician_name'
            );

        // Apply date range filter
        if ($startDateFilter) {
            $servicesQuery->whereDate('s.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $servicesQuery->whereDate('s.created_at', '<=', $endDateFilter);
        }

        // Apply technician filter
        if ($technicianFilter && $technicianFilter !== 'TODOS') {
            $servicesQuery->where('s.id_technician', $technicianFilter);
        }

        // Apply search query from DataTables to PDF query
        if (!empty($searchQuery)) {
            $servicesQuery->where(function($query) use ($searchQuery) {
                $query->where('s.code', 'like', '%' . $searchQuery . '%')
                      ->orWhere('s.ticker', 'like', '%' . $searchQuery . '%')
                      ->orWhere('s.model', 'like', '%' . $searchQuery . '%')
                      ->orWhere('s.brand', 'like', '%' . $searchQuery . '%')
                      ->orWhere('c.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('t.name', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply ordering from DataTables to PDF query
        $columnNames = [
            0 => 's.id',             // ID Servicio
            1 => 's.code',           // Código
            2 => 's.created_at',     // Fecha
            3 => 'c.name',           // Cliente
            4 => 't.name',           // Técnico
            5 => 's.status',         // Estado
            6 => 's.price',          // Precio ($)
        ];

        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            if ($orderByColumn === 's.price') {
                 $servicesQuery->orderByRaw('CAST(s.price AS DECIMAL(10, 2)) ' . ($orderDirection ?? 'asc'));
            } else {
                 $servicesQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
            }
        } else {
            // Default ordering
            $servicesQuery->orderBy('s.created_at', 'desc');
        }

        $services = $servicesQuery->get();

        $grandTotal = 0;
        foreach ($services as $service) {
            $grandTotal += $service->price ?? 0;
        }

        // Prepare filter names for the PDF
        $technicianFilterName = 'Todos los Técnicos';
        if ($technicianFilter && $technicianFilter !== 'TODOS') {
            $selectedTechnician = User::find($technicianFilter);
            if ($selectedTechnician) {
                $technicianFilterName = $selectedTechnician->name;
            }
        }

        $dateRangeFilterName = 'Todas las Fechas';
        if ($startDateFilter && $endDateFilter) {
            $dateRangeFilterName = Carbon::parse($startDateFilter)->format('d/m/Y') . ' - ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        } elseif ($startDateFilter) {
            $dateRangeFilterName = 'Desde ' . Carbon::parse($startDateFilter)->format('d/m/Y');
        } elseif ($endDateFilter) {
            $dateRangeFilterName = 'Hasta ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        }

        $pdf = PDF::loadView('pdf.service', compact('services', 'grandTotal', 'technicianFilterName', 'dateRangeFilterName', 'searchQuery', 'orderColumnIndex', 'orderDirection'));
        return $pdf->stream('services_report.pdf');
    }
    public function indexProfitPDF()
    {
            // Obtener los métodos de pago disponibles para la compañía
            $paymentMethods = PaymentMethod::get();
            return view('reports.pdfs.profit', compact('paymentMethods'));

    }

    public function ajaxProfitPDF(Request $request)
    {

        $paymentMethodTypeFilter = $request->input('payment_method_type_filter'); // Corresponds to bill_payments.type
        $startDateFilter = $request->input('start_date_filter');
        $endDateFilter = $request->input('end_date_filter');
        $searchValue = $request->input('search.value');

        $profitsQuery = Bill_payment::from('bill_payments as bp')
            ->leftJoin('users as s', 'bp.id_seller', '=', 's.id')
            ->select(
                'bp.id',
                'bp.created_at',
                'bp.type',    // 'type' del pago (e.g., 'CASH', 'TRANSFER')
                'bp.method',  // 'method' del pago (e.g., 'Efectivo', 'Transferencia Bancaria')
                'bp.reference',
                'bp.amount',  // Cantidad en USD (ganancia)
                's.name as seller_name' // Nombre del vendedor
            );

        // Apply payment method type filter
        if ($paymentMethodTypeFilter && $paymentMethodTypeFilter !== 'TODOS') {
            $profitsQuery->where('bp.type', $paymentMethodTypeFilter);
        }

        // Apply date range filter
        if ($startDateFilter) {
            $profitsQuery->whereDate('bp.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $profitsQuery->whereDate('bp.created_at', '<=', $endDateFilter);
        }

        // Custom search filter for DataTables (server-side)
        if (!empty($searchValue)) {
            $profitsQuery->where(function($query) use ($searchValue) {
                $query->where('bp.id', 'like', '%' . $searchValue . '%')
                      ->orWhere('bp.type', 'like', '%' . $searchValue . '%')
                      ->orWhere('bp.method', 'like', '%' . $searchValue . '%')
                      ->orWhere('bp.reference', 'like', '%' . $searchValue . '%')
                      ->orWhere('s.name', 'like', '%' . $searchValue . '%');
            });
        }

        return DataTables::of($profitsQuery)
            ->addColumn('formatted_date', function ($profit) {
                return (new Carbon($profit->created_at))->format('d/m/Y H:i:s');
            })
            ->addColumn('amount_formatted', function ($profit) {
                return number_format($profit->amount ?? 0, 2, '.', '');
            })
            ->addColumn('amount_raw', function ($profit) {
                return $profit->amount ?? 0;
            })
            ->rawColumns(['amount_formatted'])
            ->make(true);
    }

    public function pdfProfit(Request $request)
    {
        $paymentMethodTypeFilter = $request->input('type'); // 'type' from URL param
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        $profitsQuery = Bill_payment::from('bill_payments as bp')
            ->leftJoin('users as s', 'bp.id_seller', '=', 's.id')
            ->select(
                'bp.id',
                'bp.created_at',
                'bp.type',
                'bp.method',
                'bp.reference',
                'bp.amount',
                's.name as seller_name'
            );

        // Apply payment method type filter
        if ($paymentMethodTypeFilter && $paymentMethodTypeFilter !== 'TODOS') {
            $profitsQuery->where('bp.type', $paymentMethodTypeFilter);
        }

        // Apply date range filter
        if ($startDateFilter) {
            $profitsQuery->whereDate('bp.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $profitsQuery->whereDate('bp.created_at', '<=', $endDateFilter);
        }

        // Apply search query from DataTables to PDF query
        if (!empty($searchQuery)) {
            $profitsQuery->where(function($query) use ($searchQuery) {
                $query->where('bp.id', 'like', '%' . $searchQuery . '%')
                      ->orWhere('bp.type', 'like', '%' . $searchQuery . '%')
                      ->orWhere('bp.method', 'like', '%' . $searchQuery . '%')
                      ->orWhere('bp.reference', 'like', '%' . $searchQuery . '%')
                      ->orWhere('s.name', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply ordering from DataTables to PDF query
        $columnNames = [
            0 => 'bp.id',            // ID Pago
            1 => 'bp.created_at',    // Fecha
            2 => 'bp.type',          // Tipo de Pago
            3 => 'bp.method',        // Método
            4 => 'bp.reference',     // Referencia
            5 => 's.name',           // Vendedor
            6 => 'bp.amount',        // Monto ($)
        ];

        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            if ($orderByColumn === 'bp.amount') {
                 $profitsQuery->orderByRaw('CAST(bp.amount AS DECIMAL(10, 2)) ' . ($orderDirection ?? 'asc'));
            } else {
                 $profitsQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
            }
        } else {
            // Default ordering
            $profitsQuery->orderBy('bp.created_at', 'desc');
        }

        $profits = $profitsQuery->get();

        $grandTotal = 0;
        foreach ($profits as $profit) {
            $grandTotal += $profit->amount ?? 0;
        }

        // Prepare filter names for the PDF
        $paymentMethodTypeFilterName = 'Todos los Métodos de Pago';
        if ($paymentMethodTypeFilter && $paymentMethodTypeFilter !== 'TODOS') {
            // Find the readable name for the payment method type
            $method = PaymentMethod::where('type', $paymentMethodTypeFilter)
                                    ->select('method')
                                    ->first();
            if ($method) {
                $paymentMethodTypeFilterName = $method->method . ' (' . $paymentMethodTypeFilter . ')';
            } else {
                $paymentMethodTypeFilterName = $paymentMethodTypeFilter; // Fallback
            }
        }

        $dateRangeFilterName = 'Todas las Fechas';
        if ($startDateFilter && $endDateFilter) {
            $dateRangeFilterName = Carbon::parse($startDateFilter)->format('d/m/Y') . ' - ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        } elseif ($startDateFilter) {
            $dateRangeFilterName = 'Desde ' . Carbon::parse($startDateFilter)->format('d/m/Y');
        } elseif ($endDateFilter) {
            $dateRangeFilterName = 'Hasta ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        }

        $pdf = PDF::loadView('pdf.profit', compact('profits', 'grandTotal', 'paymentMethodTypeFilterName', 'dateRangeFilterName', 'searchQuery', 'orderColumnIndex', 'orderDirection'));
        return $pdf->stream('profits_report.pdf');
    }
     public function indexCreditPDF()
    {

            // Obtener solo los clientes que tienen facturas con saldo pendiente (payment > 0)
            $clientsWithDebt = User::whereHas('bills', function ($query) {
                                    $query->where('payment', '>', 0);
                                })
                                ->get();

            return view('reports.pdfs.credit', compact('clientsWithDebt'));
    
    }

    public function ajaxCreditPDF(Request $request)
    {

        $clientFilter = $request->input('client_filter');
        $statusFilter = $request->input('status_filter');
        $startDateFilter = $request->input('start_date_filter');
        $endDateFilter = $request->input('end_date_filter');
        $searchValue = $request->input('search.value');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id')
            // Solo facturas que no sean de tipo 'PRESUPUESTO' o 'ESPERA' si es que esos tipos nunca generan cuentas por cobrar
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA', 'FACTURA']) // Mantener por seguridad, aunque la lógica de 'payment' es la principal
            ->select(
                'b.id',
                'b.code', // Usar code
                'b.created_at',
                'b.net_amount',
                'b.payment', // Saldo Pendiente
                'b.creditDays', // Para calcular due_date y estado
                'c.name as client_name'
            );

        // Apply client filter
        if ($clientFilter && $clientFilter !== 'TODOS') {
            $billsQuery->where('b.id_client', $clientFilter);
        }

        // Apply date range filter (by bill creation date)
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Importante: El filtro por estado ('PENDING', 'PARTIAL', 'OVERDUE', 'PAID')
        // se debe hacer en la colección después de obtener los datos,
        // ya que el estado es un valor calculado.
        // Si el dataset es muy grande, esto podría ser un cuello de botella.
        // Para DataTables server-side, sería ideal que el estado se pudiera filtrar en DB.
        // Para este reporte, lo haremos en la colección como antes para simplificar.

        $bills = $billsQuery->get(); // Obtener la colección para aplicar filtros calculados

        // Filter by status on the collection
        if ($statusFilter && $statusFilter !== 'TODOS') {
            $bills = $bills->filter(function($bill) use ($statusFilter) {
                // Utiliza los accesores del modelo Bill
                $calculatedStatus = $bill->calculated_status;

                switch ($statusFilter) {
                    case 'PENDING': return $calculatedStatus == 'Pendiente';
                    case 'PARTIAL': return $calculatedStatus == 'Parcialmente Pagada';
                    case 'OVERDUE': return $calculatedStatus == 'Vencida';
                    case 'PAID':    return $calculatedStatus == 'Pagada';
                    default: return false; // Should not happen with defined filters
                }
            });
        }


        // Apply DataTables search filter on the filtered collection
        if (!empty($searchValue)) {
            $bills = $bills->filter(function($bill) use ($searchValue) {
                return (
                    stripos($bill->code, $searchValue) !== false || // Search by code
                    stripos($bill->client_name, $searchValue) !== false ||
                    stripos(number_format($bill->net_amount ?? 0, 2), $searchValue) !== false ||
                    stripos(number_format($bill->amount_paid ?? 0, 2), $searchValue) !== false ||
                    stripos(number_format($bill->payment ?? 0, 2), $searchValue) !== false || // Search on outstanding balance
                    stripos($bill->calculated_status, $searchValue) !== false
                );
            });
        }


        return DataTables::of($bills)
            ->addColumn('invoice_date', function ($bill) {
                return (new Carbon($bill->created_at))->format('d/m/Y');
            })
            ->addColumn('due_date_formatted', function ($bill) {
                // Utiliza el accesor due_date
                return $bill->due_date ? $bill->due_date->format('d/m/Y') : 'N/A';
            })
            ->addColumn('total_amount_formatted', function ($bill) {
                return number_format($bill->net_amount ?? 0, 2, '.', ''); // Usar net_amount como el monto total
            })
            ->addColumn('amount_paid_formatted', function ($bill) {
                // Utiliza el accesor amount_paid
                return number_format($bill->amount_paid ?? 0, 2, '.', '');
            })
            ->addColumn('outstanding_balance_formatted', function ($bill) {
                // Utiliza el campo payment (saldo pendiente)
                return number_format($bill->payment ?? 0, 2, '.', '');
            })
            ->addColumn('status', function ($bill) {
                // Utiliza el accesor calculated_status
                return $bill->calculated_status;
            })
            // Asegúrate de que las columnas que necesitan HTML no sean 'raw' si no lo son
            ->rawColumns(['total_amount_formatted', 'amount_paid_formatted', 'outstanding_balance_formatted'])
            ->make(true);
    }

    public function pdfCredit(Request $request)
    {
        $clientFilter = $request->input('client');
        $statusFilter = $request->input('status');
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id')
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA', 'FACTURA'])
            ->select(
                'b.id',
                'b.code',
                'b.created_at',
                'b.net_amount',
                'b.payment', // Saldo Pendiente
                'b.creditDays', // Para calcular due_date y estado
                'c.name as client_name'
            );

        // Apply client filter
        if ($clientFilter && $clientFilter !== 'TODOS') {
            $billsQuery->where('b.id_client', $clientFilter);
        }

        // Apply date range filter
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Apply search query
        if (!empty($searchQuery)) {
            $billsQuery->where(function($query) use ($searchQuery) {
                $query->where('b.code', 'like', '%' . $searchQuery . '%')
                      ->orWhere('c.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('b.net_amount', 'like', '%' . $searchQuery . '%')
                      ->orWhere('b.payment', 'like', '%' . $searchQuery . '%'); // Search on payment
            });
        }

        // Apply ordering
        $columnNames = [
            0 => 'b.code',
            1 => 'b.created_at',
            2 => 'b.creditDays', // To order by due date (needs calculation)
            3 => 'c.name',
            4 => 'b.net_amount',
            5 => 'amount_paid',  // Calculated, cannot order directly
            6 => 'b.payment',    // Outstanding balance
            7 => 'calculated_status', // Calculated, cannot order directly
        ];

        // Custom ordering logic, especially for calculated fields
        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            // Handle calculated fields or direct DB columns
            if ($orderByColumn === 'b.net_amount' || $orderByColumn === 'b.payment') {
                 $billsQuery->orderByRaw('CAST(' . $orderByColumn . ' AS DECIMAL(10, 2)) ' . ($orderDirection ?? 'asc'));
            } elseif ($orderByColumn === 'b.creditDays') { // Order by due_date
                // If ordering by creditDays, order by created_at + creditDays
                $billsQuery->orderByRaw('DATE_ADD(b.created_at, INTERVAL b.creditDays DAY) ' . ($orderDirection ?? 'asc'));
            } elseif ($orderByColumn === 'amount_paid' || $orderByColumn === 'calculated_status') {
                // These are calculated in PHP, can't order directly from DB.
                // DataTables handles client-side ordering if server-side is false for that column.
                // For PDF, we'll order after getting the collection.
            }
            else {
                 $billsQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
            }
        } else {
            $billsQuery->orderBy('b.created_at', 'desc'); // Default order
        }

        $bills = $billsQuery->get();

        // Apply status filter on the collection after fetching
        if ($statusFilter && $statusFilter !== 'TODOS') {
            $bills = $bills->filter(function($bill) use ($statusFilter) {
                return $bill->calculated_status == match($statusFilter) {
                    'PENDING' => 'Pendiente',
                    'PARTIAL' => 'Parcialmente Pagada',
                    'OVERDUE' => 'Vencida',
                    'PAID'    => 'Pagada',
                    default   => false // Fallback
                };
            });
        }

        // Calculate totals for PDF footer
        $grandTotalAmount = 0;
        $grandTotalPaid = 0;
        $grandTotalOutstanding = 0;

        foreach ($bills as $bill) {
            $grandTotalAmount += $bill->net_amount ?? 0;
            $grandTotalPaid += $bill->amount_paid ?? 0; // Use accessor
            $grandTotalOutstanding += $bill->payment ?? 0; // Use payment as outstanding
        }

        // Prepare filter names for the PDF
        $clientFilterName = 'Todos los Clientes';
        if ($clientFilter && $clientFilter !== 'TODOS') {
            $selectedClient = User::find($clientFilter);
            if ($selectedClient) {
                $clientFilterName = $selectedClient->name;
            }
        }

        $statusFilterName = 'Todos los Estados';
        if ($statusFilter && $statusFilter !== 'TODOS') {
            switch ($statusFilter) {
                case 'PENDING': $statusFilterName = 'Pendientes'; break;
                case 'PARTIAL': $statusFilterName = 'Parcialmente Pagadas'; break;
                case 'OVERDUE': $statusFilterName = 'Vencidas'; break;
                case 'PAID': $statusFilterName = 'Pagadas'; break;
                default: $statusFilterName = $statusFilter; break;
            }
        }

        $dateRangeFilterName = 'Todas las Fechas de Factura';
        if ($startDateFilter && $endDateFilter) {
            $dateRangeFilterName = Carbon::parse($startDateFilter)->format('d/m/Y') . ' - ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        } elseif ($startDateFilter) {
            $dateRangeFilterName = 'Desde ' . Carbon::parse($startDateFilter)->format('d/m/Y');
        } elseif ($endDateFilter) {
            $dateRangeFilterName = 'Hasta ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        }

        $pdf = PDF::loadView('pdf.credit', compact(
            'bills',
            'grandTotalAmount',
            'grandTotalPaid',
            'grandTotalOutstanding',
            'clientFilterName',
            'statusFilterName',
            'dateRangeFilterName',
            'searchQuery'
        ));
        return $pdf->stream('accounts_receivable_report.pdf');
    }
    public function indexEmployeePDF()
    {


            $sellers = User::whereHas('seller')->get();

            return view('reports.pdfs.employee', compact('sellers'));
     
    }
    public function ajaxEmployeePDF(Request $request)
    {

        $startDateFilter = $request->input('start_date_filter');
        $endDateFilter = $request->input('end_date_filter');
        $sellerFilter = $request->input('seller_filter');
        $searchValue = $request->input('search.value');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as s', 'b.id_seller', '=', 's.id') // Join for seller name
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id') 
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA']) // Only actual sales
            ->whereNotNull('b.id_seller') // Ensure it has a seller
            ->select(
                'b.id',
                'b.code',
                'b.net_amount',
                'b.status',
                'b.created_at',
                's.name as seller_name',
                'c.name as client_name'
            );

        // Apply date range filter
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Apply seller filter
        if ($sellerFilter && $sellerFilter !== 'TODOS') {
            $billsQuery->where('b.id_seller', $sellerFilter);
        }

        // Custom search filter for DataTables (server-side)
        if (!empty($searchValue)) {
            $billsQuery->where(function($query) use ($searchValue) {
                $query->where('b.code', 'like', '%' . $searchValue . '%')
                      ->orWhere('s.name', 'like', '%' . $searchValue . '%')
                      ->orWhere('c.name', 'like', '%' . $searchValue . '%');
            });
        }

        return DataTables::of($billsQuery)
            ->addColumn('formatted_date', function ($bill) {
                return (new Carbon($bill->created_at))->format('d/m/Y H:i:s');
            })
            ->addColumn('net_amount_formatted', function ($bill) {
                return number_format($bill->net_amount ?? 0, 2, '.', ',');
            })
            ->addColumn('net_amount_raw', function ($bill) { // Asegúrate de que esta columna existe y está correctamente definida
                return $bill->net_amount ?? 0;
            })
            ->rawColumns(['net_amount_formatted'])
            ->make(true);
    }

    public function employeePdf(Request $request)
    {

        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $sellerFilter = $request->input('seller');
        $searchQuery = $request->input('search');
        $orderColumnIndex = $request->input('order_column');
        $orderDirection = $request->input('order_dir');

        $billsQuery = Bill::from('bills as b')
            ->leftJoin('users as s', 'b.id_seller', '=', 's.id')
            ->leftJoin('users as c', 'b.id_client', '=', 'c.id')
            ->whereNotIn('b.type', ['PRESUPUESTO', 'ESPERA'])
            ->whereNotNull('b.id_seller')
            ->select(
                'b.id',
                'b.code',
                'b.net_amount',
                'b.status',
                'b.created_at',
                's.name as seller_name',
                'c.name as client_name'
            );

        // Apply date range filter
        if ($startDateFilter) {
            $billsQuery->whereDate('b.created_at', '>=', $startDateFilter);
        }
        if ($endDateFilter) {
            $billsQuery->whereDate('b.created_at', '<=', $endDateFilter);
        }

        // Apply seller filter
        if ($sellerFilter && $sellerFilter !== 'TODOS') {
            $billsQuery->where('b.id_seller', $sellerFilter);
        }

        // Apply search query from DataTables to PDF query
        if (!empty($searchQuery)) {
            $billsQuery->where(function($query) use ($searchQuery) {
                $query->where('b.code', 'like', '%' . $searchQuery . '%')
                      ->orWhere('s.name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('c.name', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply ordering from DataTables to PDF query
        $columnNames = [
            0 => 'b.id',
            1 => 'b.code',
            2 => 'b.created_at',
            3 => 'c.name',
            4 => 's.name',
            5 => 'b.status',
            6 => 'b.net_amount',
        ];

        if (isset($orderColumnIndex) && isset($columnNames[$orderColumnIndex])) {
            $orderByColumn = $columnNames[$orderColumnIndex];
            if ($orderByColumn === 'b.net_amount') {
                $billsQuery->orderByRaw('CAST(b.net_amount AS DECIMAL(10, 2)) ' . ($orderDirection ?? 'asc'));
            } else {
                $billsQuery->orderBy($orderByColumn, $orderDirection ?? 'asc');
            }
        } else {
            // Default ordering
            $billsQuery->orderBy('b.created_at', 'desc');
        }

        $bills = $billsQuery->get();

        $grandTotalAmount = 0;
        foreach ($bills as $bill) {
            $grandTotalAmount += $bill->net_amount ?? 0;
        }

        // Prepare filter names for the PDF
        $sellerFilterName = __('All Sellers');
        if ($sellerFilter && $sellerFilter !== 'TODOS') {
            $selectedSeller = User::find($sellerFilter);
            if ($selectedSeller) {
                $sellerFilterName = $selectedSeller->name;
            }
        }

        $dateRangeFilterName = __('All Dates');
        if ($startDateFilter && $endDateFilter) {
            $dateRangeFilterName = Carbon::parse($startDateFilter)->format('d/m/Y') . ' - ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        } elseif ($startDateFilter) {
            $dateRangeFilterName = __('From') . ' ' . Carbon::parse($startDateFilter)->format('d/m/Y');
        } elseif ($endDateFilter) {
            $dateRangeFilterName = __('To') . ' ' . Carbon::parse($endDateFilter)->format('d/m/Y');
        }

        $pdf = Pdf::loadView('reports.pdf.employee', compact('bills', 'grandTotalAmount', 'sellerFilterName', 'dateRangeFilterName', 'searchQuery'));
        return $pdf->stream('sales_performance_report.pdf');
    }
}
