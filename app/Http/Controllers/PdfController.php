<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\User;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Bill_payment;
use App\Models\Discount;
use App\Models\Closure;
use App\Models\Currency;
use App\Models\Dolar;
use App\Models\Repayment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Shopping;
use App\Models\Stock;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response; // Importa Response
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Etiqueta\Etiqueta;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer;
use Endroid\QrCode\Writer\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Dompdf\Dompdf;
use Dompdf\Options;

use TCPDF;

use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    public function pdf($id)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('users as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->select(
                DB::raw('DATE(bills.created_at) as date'),
                DB::raw('TIME(bills.created_at) as time'),
                'bills.id as id',
                'bills.code as code',
                'bills.type as type',
                'bills.id_currency_principal',
                'bills.id_currency_official',
                'bills.total_amount',
                'bills.discount',
                'bills.net_amount',
                'bills.rate_bill',
                'bills.rate_official',
                'bills.abbr_bill',
                'bills.abbr_official',
                'bills.abbr_principal',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
                'sellers.last_name as sellerLast_name'
            )
            ->where('bills.id', $id)
            ->first();
        $bill_details = Bill_detail::select(
            'name',
            'id',
            'price',
            'priceU',
            'net_amount',
            'quantity'
        )
            ->where('id_bill', $id)
            ->get();
        $bill_details_count = count($bill_details);
        $heightProduct = $bill_details_count * 44;
        $total =  floatval(str_replace(',', '', $bill->net_amount *  $bill->rate_official));
        $height = 330 + $heightProduct;
        $bi = $total / 1.16;
        $iva = $total - $bi;
        $pdf = PDF::setPaper([0, 0, 210, $height])->loadView('pdf.bill', [
            'bill' => $bill,
            'bi' =>   round($bi, 2),
            'iva' =>   round($iva, 2),
            'bill_details' => $bill_details,
        ]);
        return $pdf->stream();
    }
    public function pdfNoteCredit($code)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('users as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->join('repayments as repayments', 'repayments.id_bill', '=', 'bills.id')
            ->select(
                DB::raw('FORMAT(SUM(repayments.amount), 2) as total'),
                DB::raw('DATE(repayments.created_at) as date'),
                DB::raw('TIME(repayments.created_at) as time'),
                'bills.id as id',
                'bills.code as codeBill',
                'bills.type as type',
                'repayments.code as codeRepayment',
                'repayments.rate_official as rate_official',
                'repayments.abbr_official as abbr_official',
                'repayments.abbr_principal as abbr_principal',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
                'sellers.last_name as sellerLast_name'
            )
            ->where('repayments.code', $code)
            ->groupBy('repayments.code')
            ->first();
        $total =  floatval(str_replace(',', '', $bill->total *  $bill->rate_official));
        $bi = $total / 1.16;
        $iva = $total - $bi;
        $pdf = PDF::setPaper([0, 0, 226.77, 300])->loadView('pdf.noteCredit', [
            'bill' => $bill,
            'bi' =>   round($bi, 2),
            'iva' =>   round($iva, 2),
        ]);
        return $pdf->stream();
    }
    public function pdfClosure($id)
    {
        // Traer el cierre
        $closure = DB::table('closures')
            ->join('users', 'closures.id_seller', '=', 'users.id')
            ->select(
                'closures.*',
                'users.name as sellerName',
                'users.last_name as sellerLast_name',
                DB::raw('DATE(closures.created_at) as date'),
                DB::raw('TIME(closures.created_at) as time')
            )
            ->where('closures.id', $id)
            ->first();
        if ($closure->type == 'GLOBAL') {
            // 1) FACTURACIÓN (agrupado por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->where('bills.id_closure', $id)
                ->select(
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    DB::raw('SUM(bills.net_amount * bills.rate_bill) as total'),
                    DB::raw('SUM(bills.net_amount) as total_base')
                )
                ->groupBy('bills.type', 'bills.id_currency_bill', 'currencies.abbreviation', 'currencies.name', 'bills.rate_bill')
                ->get();

            $totalBill = $bills->sum('total');

            // 2) PAGOS (collection CONTADO, agrupado por moneda y tipo de pago)
            $payments = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalPayments = $payments->sum('total');

            // 3) COBRANZAS (collection CREDITO, agrupado por moneda y tipo de pago)
            $collections = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalCollections = $collections->sum('total');

            // 4) DEVOLUCIONES (agrupado por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->where('repayments.id_closure', $id)
                ->select(
                    'repayments.status',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'repayments.rate',
                    DB::raw('SUM(repayments.amount * repayments.rate) as total'),
                    DB::raw('SUM(repayments.amount) as total_base')
                )
                ->groupBy('repayments.status', 'currencies.abbreviation', 'currencies.name', 'repayments.rate')
                ->get();

            $totalRepayments = $repayments->sum('total');

            // 5) Caja chica
             $smallBox = DB::table('small_boxes')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closure', $id)
                ->select(
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('currencies.abbreviation')
                ->get();
        } else {
            // 1) FACTURACIÓN (agrupado por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->where('bills.id_closureI', $id)
                ->select(
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    DB::raw('SUM(bills.net_amount * bills.rate_bill) as total'),
                    DB::raw('SUM(bills.net_amount) as total_base')
                )
                ->groupBy('bills.type', 'bills.id_currency_bill', 'currencies.abbreviation', 'currencies.name', 'bills.rate_bill')
                ->get();

            $totalBill = $bills->sum('total');

            // 2) PAGOS (collection CONTADO, agrupado por moneda y tipo de pago)
            $payments = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalPayments = $payments->sum('total');

            // 3) COBRANZAS (collection CREDITO, agrupado por moneda y tipo de pago)
            $collections = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalCollections = $collections->sum('total');

            // 4) DEVOLUCIONES (agrupado por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->where('repayments.id_closureI', $id)
                ->select(
                    'repayments.status',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'repayments.rate',
                    DB::raw('SUM(repayments.amount * repayments.rate) as total'),
                    DB::raw('SUM(repayments.amount) as total_base')
                )
                ->groupBy('repayments.status', 'currencies.abbreviation', 'currencies.name', 'repayments.rate')
                ->get();

            $totalRepayments = $repayments->sum('total');

            // 5) Caja chica
            $smallBox = DB::table('small_boxes')
                ->where('id_closureIndividual', $id)
                ->sum('cash');
        }

        return PDF::loadView('pdf.closure', [
            'closure' => $closure,
            'bills' => $bills,
            'totalBill' => $totalBill,
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'collections' => $collections,
            'totalCollections' => $totalCollections,
            'repayments' => $repayments,
            'totalRepayments' => $totalRepayments,
            'smallBox' => $smallBox,
        ])->setPaper([0, 0, 226.77, 800])->stream('cierre.pdf');
    }

    public function pdfInventoryClosure(Request $request)
    {
        $date = $request->query('date');
        $inventoryId = $request->query('inventory_id');

        if (!$date || !$inventoryId) {
            abort(400, 'Parámetros inválidos');
        }

        // Nombre del inventario
        $inventory = DB::table('inventories')->where('id', $inventoryId)->first();

        // Consultar movimientos en stocks que estén asociados a facturas (id_bill != null)
        $rows = DB::table('stocks')
            ->join('bills', 'stocks.id_bill', '=', 'bills.id')
            ->join('bill_details', function ($join) {
                $join->on('bill_details.id_bill', '=', 'bills.id')
                    ->on('bill_details.id_product', '=', 'stocks.id_product');
            })
            ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
            ->join('users as clients', 'bills.id_client', '=', 'clients.id')
            ->where('stocks.id_inventory', $inventoryId)
            ->whereNotNull('stocks.id_bill')
            ->whereRaw('DATE(stocks.created_at) = ?', [$date])
            ->select(
                'bills.code as bill_code',
                DB::raw("CONCAT(sellers.name, ' ', sellers.last_name) as seller"),
                DB::raw("CONCAT(clients.name, ' ', clients.last_name) as client"),
                'bill_details.code as product_code',
                'bill_details.name as product_name',
                'bill_details.quantity',
                'bill_details.net_amount'
            )
            ->orderBy('bills.code')
            ->get();

        $generatedBy = auth()->user() ? auth()->user()->name . ' ' . auth()->user()->last_name : '';

        $data = [
            'inventory' => $inventory,
            'date' => $date,
            'rows' => $rows,
            'generatedBy' => $generatedBy,
            'generatedAt' => date('Y-m-d H:i:s'),
        ];

        $pdf = PDF::loadView('pdf.inventoryClosure', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('cierre_inventario_' . $inventoryId . '_' . $date . '.pdf');
    }

    public function pdfClosureDetail($id)
    {
        // Traer el cierre
        $closure = DB::table('closures')
            ->join('users', 'closures.id_seller', '=', 'users.id')
            ->select(
                'closures.*',
                'users.name as sellerName',
                'users.last_name as sellerLast_name',
                DB::raw('DATE(closures.created_at) as date'),
                DB::raw('TIME(closures.created_at) as time')
            )
            ->where('closures.id', $id)
            ->first();
        if ($closure->type == 'GLOBAL') {
            // 1) FACTURACIÓN (detalles agrupados por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bills.id_closure', $id)
                ->select(
                    'bills.id',
                    'bills.code',
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    'bills.total_amount',
                    'bills.discount',
                    'bills.net_amount',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('bills.id_currency_bill')
                ->orderBy('bills.type')
                ->get();

            $billsGrouped = $bills->groupBy(['abbr', 'type']);

            // 2) PAGOS (CONTADO, detalles agrupados por moneda y tipo)
            $bill_paymentContado = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $paymentsGrouped = $bill_paymentContado->groupBy(['abbr', 'type']);

            // 3) COBRANZAS (CREDITO, detalles agrupados por moneda y tipo)
            $bill_paymentCredito = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $collectionsGrouped = $bill_paymentCredito->groupBy(['abbr', 'type']);

            // 4) DEVOLUCIONES (detalles agrupados por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->join('bills', 'repayments.id_bill', '=', 'bills.id')
                ->join('products', 'repayments.id_product', '=', 'products.id')
                ->where('repayments.id_closure', $id)
                ->select(
                    'repayments.id',
                    'repayments.amount',
                    'repayments.rate',
                    'repayments.status',
                    'repayments.quantity',
                    'repayments.code',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'products.code as product_code',
                    'products.name as product_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('repayments.status')
                ->get();

            $repaymentsGrouped = $repayments->groupBy(['abbr', 'status']);

            // 5) Caja chica
            $smallBox = DB::table('small_boxes')
                ->join('users as empleados', 'small_boxes.id_employee', '=', 'empleados.id')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closure', $id)
                ->select(
                    'empleados.id',
                    'empleados.name as employee_name',
                    'empleados.last_name as employee_last_name',
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('empleados.id', 'empleados.name', 'empleados.last_name', 'currencies.abbreviation')
                ->get();
        } else {
            // 1) FACTURACIÓN (detalles agrupados por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bills.id_closureI', $id)
                ->select(
                    'bills.id',
                    'bills.code',
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    'bills.total_amount',
                    'bills.discount',
                    'bills.net_amount',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('bills.id_currency_bill')
                ->orderBy('bills.type')
                ->get();

            // Agrupación para la vista
            $billsGrouped = $bills->groupBy(['abbr', 'type']);

            // 2) PAGOS (CONTADO, detalles agrupados por moneda y tipo)
            $bill_paymentContado = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $paymentsGrouped = $bill_paymentContado->groupBy(['abbr', 'type']);

            // 3) COBRANZAS (CREDITO, detalles agrupados por moneda y tipo)
            $bill_paymentCredito = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('users as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'sellers.last_name as seller_last_name',
                    'clients.name as client_name',
                    'clients.last_name as client_last_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $collectionsGrouped = $bill_paymentCredito->groupBy(['abbr', 'type']);

            // 4) DEVOLUCIONES (detalles agrupados por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->join('bills', 'repayments.id_bill', '=', 'bills.id')
                ->join('products', 'repayments.id_product', '=', 'products.id')
                ->where('repayments.id_closureI', $id)
                ->select(
                    'repayments.id',
                    'repayments.amount',
                    'repayments.rate',
                    'repayments.status',
                    'repayments.quantity',
                    'repayments.code',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'products.code as product_code',
                    'products.name as product_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('repayments.status')
                ->get();

            $repaymentsGrouped = $repayments->groupBy(['abbr', 'status']);

            // 5) Caja chica
            $smallBox = DB::table('small_boxes')
                ->join('users as empleados', 'small_boxes.id_employee', '=', 'empleados.id')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closureIndividual', $id)
                ->select(
                    'empleados.name as employee_name',
                    'empleados.last_name as employee_last_name',
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('empleados.id', 'empleados.name', 'empleados.last_name', 'currencies.abbreviation')
                ->get();
        }


        return PDF::loadView('pdf.closureDetail', [
            'closure' => $closure,
            'billsGrouped' => $billsGrouped,
            'bill_paymentContadoGrouped' => $paymentsGrouped,
            'bill_paymentCreditoGrouped' => $collectionsGrouped,
            'repaymentsGrouped' => $repaymentsGrouped,
            'smallBox' => $smallBox,
        ])->setPaper('letter', 'portrait')->stream('cierre_detallado.pdf');
    }
    public function pdfTicket($id)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $service = DB::table('services')
            ->join('users as clients', 'clients.id', '=', 'services.id_client')
            ->leftJoin('users as technician', 'technician.id', '=', 'services.id_technician')
            ->join('users as sellers', 'sellers.id', '=', 'services.id_seller')
            ->join('service_categories as categories', 'categories.id', '=', 'services.id_category')
            ->select(
                DB::raw('DATE(services.created_at) as date'),
                DB::raw('TIME(services.created_at) as time'),
                'services.id as id',
                'services.ticker as ticker',
                'services.model as model',
                'services.brand as brand',
                'services.serial as serial',
                'services.description as description',
                'services.solution as solution',
                'services.status as status',
                'services.price as price',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'technician.name as technicianName',
                'technician.last_name as technicianLast_name',
                'sellers.name as sellersName',
                'sellers.last_name as sellersLast_name',
                'categories.name as category'
            )
            ->where('services.id', $id)
            ->first();
        if (!$service) {
            // Manejar el caso en que no se encuentra el servicio
            return abort(404, 'Servicio no encontrado'); // Ejemplo con código de error 404
        }
        $service2 = Service::with(['serviceDetails' => function ($query) {
            $query->with('product'); // Carga la relación 'product' dentro de 'serviceDetails'
        }])->find($id);
        $totalPrice = $service2->serviceDetails->sum('price');
        $serviceDetailsConNombreProducto = $service2->serviceDetails->map(function ($serviceDetail) {
            return [
                'id' => $serviceDetail->id,
                'quantity' => $serviceDetail->quantity,
                'price' => $serviceDetail->price,
                'procedure' => Str::limit($serviceDetail->procedure, 38),
                'id_product' => $serviceDetail->id_product,
                'product_name' => $serviceDetail->product ? Str::limit($serviceDetail->product->name, 38) : null, // Obtiene el nombre del producto o null si no existe
            ];
        });
        // Generate QR code with ticker information

        $writer = new PngWriter();

        // Create QR code
        $qrCode = new QrCode(
            data: 'https://www.telematicstech.net/pdfTicket/' . $service->id,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 100,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $result = $writer->write($qrCode);
        $qrCodeImage = $result->getDataUri();

        $bill_details_count = count($service2->serviceDetails);
        $heightProduct = $bill_details_count * 24;
        $height = 420 + $heightProduct;

        $pdf = PDF::setPaper([0, 0, 210, $height])->loadView('pdf.ticket', [
            'service' => $service,
            'serviceDetails' => $serviceDetailsConNombreProducto,
            'totalPrice' => $totalPrice,
            'qrCode' => $qrCodeImage,
        ]);
        return $pdf->stream();
    }
    public function pdfBillService($id)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $service = DB::table('services')
            ->join('users as clients', 'clients.id', '=', 'services.id_client')
            ->leftJoin('users as technician', 'technician.id', '=', 'services.id_technician')
            ->join('users as companies', 'companies.id', '=', 'services.id_company')
            ->join('service_categories as categories', 'categories.id', '=', 'services.id_category')
            ->select(
                DB::raw('DATE(services.created_at) as date'),
                DB::raw('TIME(services.created_at) as time'),
                'services.id as id',
                'services.ticker as ticker',
                'services.model as model',
                'services.brand as brand',
                'services.serial as serial',
                'services.description as description',
                'services.price as price',
                'services.priceBs as priceBs',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'technician.name as technicianName',
                'technician.last_name as technicianLast_name',
                DB::raw('LEFT(companies.name, 35) AS companyName'),
                DB::raw('LEFT(companies.direction, 40) AS companyDirection'),
                'companies.nationality as companyNationality',
                'companies.ci as companyCi',
                'companies.state as companyState',
                'companies.city as companyCity',
                'companies.postal_zone as companyPostal_zone',
                'categories.name as category'
            )
            ->where('services.id', $id)
            ->first();
        if (!$service) {
            // Manejar el caso en que no se encuentra el servicio
            return abort(404, 'Servicio no encontrado'); // Ejemplo con código de error 404
        }
        $service2 = Service::with(['serviceDetails' => function ($query) {
            $query->with('product'); // Carga la relación 'product' dentro de 'serviceDetails'
        }])->find($id);

        $serviceDetailsConNombreProducto = $service2->serviceDetails->map(function ($serviceDetail) {
            return [
                'id' => $serviceDetail->id,
                'quantity' => $serviceDetail->quantity,
                'price' => $serviceDetail->price,
                'priceBs' => $serviceDetail->priceBs,
                'procedure' => $serviceDetail->procedure,
                'id_product' => $serviceDetail->id_product,
                'product_name' => $serviceDetail->product ? $serviceDetail->product->name : null, // Obtiene el nombre del producto o null si no existe
            ];
        });
        $priceBs =  floatval(str_replace(',', '', $service->priceBs));
        $bi = $priceBs / 1.16;
        $iva = $priceBs - $bi;
        $pdf = PDF::setPaper([0, 0, 210, 400])->loadView('pdf.billService', [
            'service' => $service,
            'serviceDetails' => $serviceDetailsConNombreProducto,
            'bi' =>   round($bi, 2),
            'iva' =>   round($iva, 2),
        ]);
        return $pdf->stream();
    }

    public function pdfStock($id_inventory_adjustment)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code', 'products.price as product_price')
                ->join('products', 'stocks.id_product', '=', 'products.id')
                ->where('stocks.id_inventory_adjustment', $id_inventory_adjustment)
                ->get();

            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este ajuste.'], 404);
            }

            // Obtener datos del usuario
            $user = User::find($stock->first()->id_user);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }

            // Calcular diferencias y montos
            $amount_lost = 0;
            $amount_profit = 0;
            $stockConDiferencia = $stock->map(function ($item) use (&$amount_lost, &$amount_profit) {
                // Stock anterior
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->where('id', '<', $item->id)
                    ->orderBy('id', 'desc')
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                $monto = abs($diferencia) * $item->product_price;
                if ($diferencia < 0) $amount_lost += $monto;
                if ($diferencia > 0) $amount_profit += $monto;
                return [
                    'id' => $item->id,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'diferencia' => $diferencia,
                    'product_price' => $item->product_price,
                    'monto' => $monto,
                ];
            });

            $total = $amount_profit - $amount_lost;

            $pdf = PDF::loadView('pdf.stock', [
                'user' => $user,
                'stock' => $stockConDiferencia,
                'amount_lost' => $amount_lost,
                'amount_profit' => $amount_profit,
                'total' => $total,
                'ajuste_id' => $id_inventory_adjustment
            ]);
            $pdf->setPaper('letter', 'portrait');
            return $pdf->stream('ajuste_inventario.pdf');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
    public function pdfLabel($id, $quantity)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            $product = Product::find($id);

            $generator = new BarcodeGeneratorPNG();
            $barcodePNG = base64_encode($generator->getBarcode($product->code, $generator::TYPE_CODE_128, 2, 30));

            $height = ($quantity * 70);

            // $pdf = PDF::setPaper([0, 0, 141.73, 42.52])->loadView('pdf.label', [
            $pdf = PDF::setPaper([0, 0, 145.73, 52.52])->loadView('pdf.label', [
                'product' => $product,
                'quantity' => $quantity,
                'barcodePNG' => $barcodePNG,
            ]);
            return $pdf->stream();
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al generar etiqueta: ' . $e->getMessage()], 500);
        }
    }
    public function pdfLabelAll($code)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code') // Selecciona los campos necesarios
                ->join('products', 'stocks.id_product', '=', 'products.id') // Realiza el JOIN
                ->where('stocks.id_shopping', $code)
                ->get();
            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este código y compañía.'], 404);
            }
            $labels = $stock->map(function ($item) {
                // Subconsulta para obtener el stock anterior
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->where('id', '<', $item->id) // Stock con ID menor (anterior)
                    ->orderBy('id', 'desc') // Ordena descendente para obtener el más reciente
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                if ($diferencia > 0) {
                    $generator = new BarcodeGeneratorPNG();
                    $barcodePNG = base64_encode($generator->getBarcode($item->product_code, $generator::TYPE_CODE_128, 2, 30));
                    return [
                        'code' => $item->product_code, // Usa el code del producto
                        'name' => $item->product_name, // Usa el nombre del producto
                        'quantity' => $diferencia,
                        'barcodePNG' => $barcodePNG,
                    ];
                }
            });


            // $pdf = PDF::setPaper([0, 0, 141.73, 42.52])->loadView('pdf.label', [
            $pdf = PDF::setPaper([0, 0, 145.73, 52.52])->loadView('pdf.labelAll', [
                'labels' => $labels
            ]);
            return $pdf->stream();
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al generar etiqueta: ' . $e->getMessage()], 500);
        }
    }
    public function pdfProduct(Request $request)
    {
        if (auth()->user()->type == 'ADMINISTRADOR') {
            $id_compan = auth()->id();
        } else if (auth()->user()->type == 'EMPRESA') {
            $id_compan = auth()->id();
        } else if (auth()->user()->type == 'EMPLEADO' || auth()->user()->type == 'SUPERVISOR' || auth()->user()->type == 'ADMINISTRATIVO') {
            $id_company = Employee::select('id_company')->where('id_employee', auth()->id())->first();
            $id_compan =  $id_company->id_company;
        }
        $type = $request->input('type');

        $products = Product::from('products')
            ->joinSub(function ($query) {
                $query->from('stocks')
                    ->select('stocks.id_product', DB::raw('MAX(stocks.created_at) as ultimo_stock'))
                    ->groupBy('stocks.id_product');
            }, 'ultimo_stock', function ($join) {
                $join->on('products.id', '=', 'ultimo_stock.id_product');
            })
            ->leftJoin('stocks', function ($join) {
                $join->on('products.id', '=', 'stocks.id_product')
                    ->on('stocks.created_at', '=', 'ultimo_stock.ultimo_stock');
            })
            ->where('products.id_company', $id_compan);

        if ($type === 'TODOS') {
            $products->where('stocks.quantity', '>', 0);
        } elseif ($type === 'SINSTOCK') {
            $products->where('stocks.quantity', '=', 0);
        } elseif ($type === 'NEGATIVOS') {
            $products->where('stocks.quantity', '<', 0);
        }

        $products = $products->select('products.id', 'products.name', 'products.price', 'stocks.created_at', 'stocks.quantity', 'products.code')
            ->orderBy('products.created_at', 'desc')
            ->get(); // Get the results as a Collection

        $total = 0;
        foreach ($products as $product) {
            $total += ($product->quantity ?? 0) * $product->price;
        }

        $pdf = PDF::loadView('pdf.product', compact('products', 'total', 'type')); // Load your PDF view
        return $pdf->stream('products.pdf'); // Stream the PDF to the browser
    }
    public function pdfShopping($id)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code') // Selecciona los campos necesarios
                ->join('products', 'stocks.id_product', '=', 'products.id') // Realiza el JOIN
                ->where('stocks.id_shopping', $id)
                ->get();
            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este código y compañía.'], 404);
            }
            // Obtener datos de la compañía (una sola vez)
            // Obtener datos del usuario (asumiendo que todos los stocks tienen el mismo usuario, se toma el primero)
            $shopping = Shopping::find($id);
            $user = User::find($shopping->id_user);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }

            // Calcular la diferencia con el stock anterior
            $pdf = PDF::loadView('pdf.shopping', [
                'code' => $id,
                'user' => $user,
                'stock' => $stock,
                'shopping' => $shopping,
            ]);
            $pdf->setPaper('letter', 'portrait'); // Set paper size to letter and portrait orientation
            return $pdf->stream('campras.pdf');
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
    public function pdfPayment($id)
    {
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('users as clients', 'clients.id', '=', 'bills.id_client')
            ->join('bill_payments as payments', 'payments.id_bill', '=', 'bills.id')
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.id_payment_method')
            ->join('currencies as curr', 'curr.id', '=', 'pm.id_currency')
            ->select(
                DB::raw('DATE(payments.created_at) as date'),
                DB::raw('TIME(payments.created_at) as time'),
                'bills.id as id',
                'bills.code as codeBill',
                'payments.amount as amount',
                'payments.rate as rate',
                'curr.abbreviation as currency_abbr',
                'pm.type as payment_type',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction'
            )
            ->where('payments.id', $id)
            ->first();

        // Abreviatura de moneda principal
        $principal_currency = DB::table('currencies')
            ->where('is_principal', 1)
            ->value('abbreviation');

        $total = floatval($bill->amount) * floatval($bill->rate);
        $bi = $total / 1.16;
        $iva = $total - $bi;

        $pdf = PDF::setPaper([0, 0, 226.77, 300])->loadView('pdf.payment', [
            'bill' => $bill,
            'bi' => round($bi, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
            'principal_currency' => $principal_currency,
        ]);
        return $pdf->stream();
    }
    public function pdfGuarantee($id)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $guarantee = DB::table('guarantees')
            ->join('users as companies', 'companies.id', '=', 'guarantees.id_company')
            ->select(
                DB::raw('DATE(guarantees.created_at) as date'),
                DB::raw('TIME(guarantees.created_at) as time'),
                'guarantees.code as code',
                'guarantees.name as name',
                'guarantees.serial as serial',
                'guarantees.description as description',
                'guarantees.status as status',
                DB::raw('LEFT(companies.name, 35) AS companyName'),
                DB::raw('LEFT(companies.direction, 40) AS companyDirection'),
                'companies.nationality as companyNationality',
                'companies.ci as companyCi',
                'companies.state as companyState',
                'companies.city as companyCity',
                'companies.postal_zone as companyPostal_zone'
            )
            ->where('guarantees.id', $id)
            ->first();

        $pdf = PDF::setPaper([0, 0, 226.77, 250])->loadView('pdf.guarantee', [
            'guarantee' => $guarantee
        ]);
        return $pdf->stream();
    }
    public function pdfCatalog(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(900);
        DB::statement("SET SQL_MODE=''");

        $productsQuery = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'products.id_company as id_company', 'url1', 'name', 'status')
            ->where('status', '!=', '0')
            ->where('products.id_company', $request->id_company);

        // ... [tu lógica de filtrado por category, scope, id_inventory, etc., es la misma] ...

        if ($request->category != '' && $request->category != 'TODAS') {
            $productsQuery->join('add_categories', 'add_categories.id_product', '=', 'products.id')
                ->where('add_categories.id_category', $request->category);
        }

        if ($request->scope != '') {
            $productsQuery->where('products.name', 'like', "%$request->scope%");
        }

        $selectedInventoryId = $request->input('id_inventory');
        if ($selectedInventoryId) {
            $productsQuery->whereHas('stocks', function ($query) use ($selectedInventoryId) {
                $query->where('id_inventory', $selectedInventoryId);
            });
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['asc', 'desc', 'available', 'unavailable'])) {
            $productsQuery = $this->sortProductsByStock($productsQuery, $request->sort_by, $selectedInventoryId);
        } else {
            $productsQuery->orderByDesc('products.updated_at');
        }

        // Obtener TODOS los productos filtrados y ordenados, no paginados
        $products = $productsQuery->get(); // <-- Cambiado de paginate a get()

        // Asignar el stock de inventario a cada producto (igual que en indexStoreAjax)
        foreach ($products as $product) {
            $latestStock = DB::table('stocks')
                ->where('id_product', $product->id)
                ->when($selectedInventoryId, function ($query) use ($selectedInventoryId) {
                    return $query->where('id_inventory', $selectedInventoryId);
                })
                ->latest()
                ->first();
            $product->stock = $latestStock ? $latestStock->quantity : 0;
        }

        $dolar = Dolar::where('id_company', $request->id_company)
            ->latest()
            ->first();

        // Si no hay dólar, puedes manejarlo aquí (ej. retornar error o asignar valores por defecto)
        if (!$dolar) {
            // Manejo de error si no se encuentra el dólar
            // return response('No se pudo generar el catálogo: Precio del dólar no disponible.', 400);
            // O asignar un objeto dólar por defecto para que la vista no falle
            $dolar = (object)['priceBs' => 1, 'price' => 1];
        }

        // Cargar la vista con TODOS los productos en un solo PDF
        $pdf = PDF::loadView('pdf.catalog', compact('products', 'dolar')); // <-- products (no productsPage)
        return $pdf->stream('catalogo.pdf'); // O download('catalogo.pdf') si quieres forzar la descarga
    }

    // Asegúrate de que esta función sea la misma que la que tienes en indexStoreAjax
    private function sortProductsByStock($products, $sortBy, $selectedInventoryId = null)
    {
        $stockSubquery = '(SELECT quantity FROM stocks
                        WHERE id_product = products.id
                        ' . ($selectedInventoryId ? 'AND id_inventory = ' . (int)$selectedInventoryId : '') . '
                        ORDER BY created_at DESC LIMIT 1)';

        $products->addSelect(DB::raw($stockSubquery . ' as stock_value'));

        switch ($sortBy) {
            case 'asc':
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) ASC");
                break;
            case 'desc':
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) DESC");
                break;
            case 'available':
                $products->whereRaw($stockSubquery . ' > 0');
                break;
            case 'unavailable':
                $products->whereRaw($stockSubquery . ' < 1');
                break;
        }

        return $products;
    }
    public function pdfCreditPayment($id)
    {
        // Trae la factura
        $bill = DB::table('bills')
            ->join('users as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->select(
                DB::raw('DATE(bills.created_at) as date'),
                DB::raw('TIME(bills.created_at) as time'),
                'bills.id as id',
                'bills.code as code',
                'bills.type as type',
                'bills.total_amount',
                'bills.discount',
                'bills.net_amount',
                'bills.abbr_bill',
                'bills.abbr_official',
                'bills.abbr_principal',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
                'sellers.last_name as sellerLast_name'
            )
            ->where('bills.id', $id)
            ->first();

        // Trae los pagos realizados
        $payments = DB::table('bill_payments')
            ->leftJoin('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
            ->leftJoin('currencies', 'currencies.id', '=', 'payment_methods.id_currency')
            ->select(
                'bill_payments.amount',
                'bill_payments.created_at',
                'bill_payments.reference',
                'bill_payments.code_repayment',
                'payment_methods.type as payment_type',
                'payment_methods.bank as payment_bank',
                'currencies.abbreviation as currency_abbr',
            )
            ->where('bill_payments.id_bill', $id)
            ->orderBy('bill_payments.created_at')
            ->get();

        $totalPagado = $payments->sum('amount');
        $restante = $bill->net_amount - $totalPagado;

        return Pdf::setPaper([0, 0, 226.77, 600])
            ->loadView('pdf.credit-payment', [
                'bill' => $bill,
                'payments' => $payments,
                'totalPagado' => $totalPagado,
                'restante' => $restante
            ])->stream('detalle_pagos_credito.pdf');
    }
    public function pdfRepaymentDetail($code)
    {
        // Trae la cabecera del repayment
        $repayment = DB::table('repayments')
            ->join('users as clients', 'clients.id', '=', 'repayments.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'repayments.id_seller')
            ->join('bills', 'bills.id', '=', 'repayments.id_bill')
            ->select(
                'repayments.code as code',
                'repayments.created_at as created_at',
                'repayments.status as status',
                'bills.code as codeBill',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
                'sellers.last_name as sellerLast_name'
            )
            ->where('repayments.code', $code)
            ->first();

        // Trae los productos devueltos en ese repayment
        $products = DB::table('repayments')
            ->join('products', 'products.id', '=', 'repayments.id_product')
            ->select(
                'products.name as product_name',
                'repayments.quantity',
                'repayments.amount'
            )
            ->where('repayments.code', $code)
            ->get();

        $total = $products->sum(function ($item) {
            return $item->amount * $item->quantity;
        });

        return Pdf::setPaper([0, 0, 226.77, 400])
            ->loadView('pdf.repayment-detail', [
                'repayment' => $repayment,
                'products' => $products,
                'total' => $total
            ])->stream('detalle_reembolso.pdf');
    }
}
