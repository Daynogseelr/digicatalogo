<?php

namespace App\Http\Controllers;

use App\DataTables\BillDataTable;
use App\Models\User;
use App\Models\Bill;
use App\Models\Bill_payment;
use App\Models\PaymentMethod;
use App\Models\Repayment;
use App\Models\Bill_detail;
use App\Models\Product;
use App\Models\ProductIntegral;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(BillDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $paymentMethods = PaymentMethod::where('status', 1)->get();
            $clients = User::where('type', '!=', 'ADMINISTRADOR')->get();
            return $dataTable->render('bills.bill', compact('clients', 'paymentMethods'));
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function mostrarBillPayment(Request $request)
    {
        $bill = Bill::find($request->id);
        $bill_payments = Bill_payment::with(['paymentMethod.currency'])
            ->where('id_bill', '=', $bill->id)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'type' => $p->paymentMethod->type ?? '',
                    'currency_abbreviation' => $p->paymentMethod->currency->abbreviation ?? '',
                    'currency_type' => $p->paymentMethod->currency->type ?? '',
                    'reference' => $p->reference,
                    'amount' => $p->amount,
                    'rate' => $p->rate,
                ];
            });
        return response()->json([
            'bill_payments' => $bill_payments,
            'payment' => $bill->payment
        ]);
    }
    public function modalRepayment(Request $request)
    {
        $bill = Bill::with('bill_details.product')->find($request->id);

        // Productos ya devueltos agrupados por id_product
        $returned = Repayment::where('id_bill', $bill->id)
            ->select('id_product', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('id_product')
            ->pluck('quantity', 'id_product');

        // Productos normales con cantidad pendiente
        $products = $bill->bill_details
            ->filter(function ($detail) {
                return $detail->id_product != null && $detail->product != null;
            })
            ->map(function ($detail) use ($returned) {
                $devuelto = $returned[$detail->id_product] ?? 0;
                return [
                    'id' => $detail->id,
                    'id_product' => $detail->id_product,
                    'code' => $detail->product->code,
                    'name' => $detail->product->name,
                    'quantity' => $detail->quantity - $devuelto,
                    'price' => $detail->price,
                ];
            })
            ->values();

        // Productos ya devueltos (histórico)
        $returned_products = Repayment::where('id_bill', $bill->id)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'code' => $r->code,
                    'name' => $r->product->name ?? '',
                    'quantity' => $r->quantity,
                    'price' => $r->amount,
                ];
            });

        return response()->json([
            'bill' => $bill,
            'products' => $products,
            'returned_products' => $returned_products,
        ]);
    }

    public function saveRepayment(Request $request)
    {
        $returnedProducts = $request->returnedProducts;
        $id_bill = $request->id_bill;
        $michek = $request->michek;
        $id_currency = $request->id_currency;
        $rate = $request->rate;
        $rate_official = $request->rate_official;
        $abbr_repayment = $request->abbr_repayment;
        $abbr_official = $request->abbr_official;
        $abbr_principal = $request->abbr_principal;

        if (!is_array($returnedProducts) || count($returnedProducts) == 0) {
            return response()->json(['res' => 'mal', 'msg' => 'No hay productos a devolver']);
        }

        $bill = Bill::select('payment', 'id_client')->where('id', $id_bill)->first();
        $repayments_last = Repayment::max(DB::raw('CAST(code AS UNSIGNED)'));
        $codeNew = $repayments_last ? $repayments_last + 1 : 1;

        $totalAmount = 0;
        $lastRepayment = null;

        foreach ($returnedProducts as $prod) {
            $bill_detail = Bill_detail::select('id', 'id_bill', 'id_product', 'id_inventory', 'quantity', 'net_amount', 'name')
                ->where('id', $prod['id'])
                ->where('id_bill', $id_bill)
                ->first();
            if (!$bill_detail) continue;

            $product = Product::find($bill_detail->id_product);

            // Verifica cantidad máxima a devolver considerando devoluciones previas
            $repayment = Repayment::where('id_bill', $bill_detail->id_bill)
                ->where('id_product', $bill_detail->id_product)
                ->sum('quantity');
            $maxQuantity = $bill_detail->quantity - $repayment;
            $quantityToReturn = intval($prod['quantity']);
            if ($quantityToReturn < 1 || $quantityToReturn > $maxQuantity) {
                continue;
            }

            // Calcula el monto y la cantidad real para fraccionados
            $realQuantity = $quantityToReturn;
            if ($product->type == 'FRACCIONADO' && $bill_detail->name == $product->name_fraction && $product->equivalence_fraction > 0) {
                $realQuantity = $quantityToReturn / $product->equivalence_fraction;
            }

            $amount = ($bill_detail->net_amount / $bill_detail->quantity) * $quantityToReturn;
            $totalAmount += $amount;

            $lastRepayment = Repayment::create([
                'id_bill' =>  $bill_detail->id_bill,
                'id_product' => $bill_detail->id_product,
                'id_client' => $bill->id_client,
                'id_seller' => auth()->id(),
                'id_inventory' => $bill_detail->id_inventory,
                'id_currency' => $id_currency,
                'rate' => $rate,
                'rate_official' => $rate_official,
                'abbr_repayment' => $abbr_repayment,
                'abbr_official' => $abbr_official,
                'abbr_principal' => $abbr_principal,
                'code' => $codeNew,
                'quantity' => $quantityToReturn,
                'amount' => $amount,
                'status' => $michek,
            ]);

            // Actualiza el stock
            $stock = Stock::where('id_product', $bill_detail->id_product)
                ->where('id_inventory', $bill_detail->id_inventory)
                ->latest()
                ->first();
            if ($stock) {
                Stock::create([
                    'id_product' => $bill_detail->id_product,
                    'id_user' => auth()->id(),
                    'id_inventory' => $bill_detail->id_inventory,
                    'id_repayment' => $lastRepayment->id,
                    'addition' => $realQuantity,
                    'subtraction' => 0,
                    'quantity' => $stock->quantity + $realQuantity,
                    'description' => 'DEVOLUCION DE MERCANCIA Nº' . $codeNew,
                ]);
            }
            // DEVOLVER COMPONENTES SI ES INTEGRAL
            if ($product->type == 'INTEGRAL') {
                $integrals = ProductIntegral::where('id_product', $product->id)->get();
                foreach ($integrals as $integral) {
                    $componentProduct = Product::find($integral->id_productI);
                    $componentQuantity = $integral->quantity * $quantityToReturn;

                    // Busca el inventario del componente (puedes usar el mismo inventario del principal)
                    $componentInventory = $bill_detail->id_inventory;

                    // Actualiza el stock del componente
                    $componentStock = Stock::where('id_product', $componentProduct->id)
                        ->where('id_inventory', $componentInventory)
                        ->latest()
                        ->first();
                    if ($componentStock) {
                        Stock::create([
                            'id_product' => $componentProduct->id,
                            'id_user' => auth()->id(),
                            'id_inventory' => $componentInventory,
                            'id_repayment' => $lastRepayment->id,
                            'addition' => $componentQuantity,
                            'subtraction' => 0,
                            'quantity' => $componentStock->quantity + $componentQuantity,
                            'description' => 'DEVOLUCION COMPONENTE INTEGRAL Nº' . $codeNew,
                        ]);
                    }

                    // Registra la devolución del componente en la tabla Repayment (opcional, si quieres histórico)
                    Repayment::create([
                        'id_bill' =>  $bill_detail->id_bill,
                        'id_product' => $componentProduct->id,
                        'id_client' => $bill->id_client,
                        'id_seller' => auth()->id(),
                        'id_inventory' => $componentInventory,
                        'id_currency' => $id_currency,
                        'rate' => $rate,
                        'rate_official' => $rate_official,
                        'abbr_repayment' => $abbr_repayment,
                        'abbr_official' => $abbr_official,
                        'abbr_principal' => $abbr_principal,
                        'code' => $codeNew,
                        'quantity' => $componentQuantity,
                        'amount' => 0, // Si quieres calcular el monto, hazlo aquí
                        'status' => $michek,
                    ]);
                }
            }
        }

        // Actualiza el pago si corresponde
        $bill = Bill::find($id_bill);
        if ($bill->payment > 0 && $totalAmount > 0) {
            $bill->payment -= $totalAmount;
            $bill->save();
        }

        return response()->json([
            'id' => $lastRepayment ? $lastRepayment->code : null,
            'id_bill' => $id_bill,
            'res' => 'bien'
        ]);
    }

    public function saveRepaymentAll(Request $request)
    {
        $id_currency = $request->id_currency;
        $rate = $request->rate;
        $rate_official = $request->rate_official;
        $abbr_repayment = $request->abbr_repayment;
        $abbr_official = $request->abbr_official;
        $abbr_principal = $request->abbr_principal;
        $bill_details = Bill_detail::select('id_bill', 'id_product', 'id_inventory', 'quantity', 'net_amount', 'name')
            ->where('id_bill', $request->id_bill)
            ->get();
        $bill = Bill::find($request->id_bill);
        $bill->update(['type' => 'ANULADA', 'payment' => 0]);
        $repayments_last = Repayment::max(DB::raw('CAST(code AS UNSIGNED)'));
        $codeNew = $repayments_last ? $repayments_last + 1 : 1;

        foreach ($bill_details as $bill_detail) {
            $product = Product::find($bill_detail->id_product);
            $repayment = Repayment::where('id_bill', $bill_detail->id_bill)
                ->where('id_product', $bill_detail->id_product)
                ->sum('quantity');
            $maxQuantity = $bill_detail->quantity - $repayment;
            if ($maxQuantity > 0) {
                $realQuantity = $maxQuantity;
                if ($product && $product->type == 'FRACCIONADO' && $bill_detail->name == $product->name_fraction && $product->equivalence_fraction > 0) {
                    $realQuantity = $maxQuantity / $product->equivalence_fraction;
                }

                $amount = ($bill_detail->net_amount / $bill_detail->quantity) * $maxQuantity;
                $repaymentNew = Repayment::create([
                    'id_bill' =>  $bill_detail->id_bill,
                    'id_product' => $bill_detail->id_product,
                    'id_client' => $bill->id_client,
                    'id_seller' => auth()->id(),
                    'id_inventory' => $bill_detail->id_inventory,
                    'id_currency' => $id_currency,
                    'rate' => $rate,
                    'rate_official' => $rate_official,
                    'abbr_repayment' => $abbr_repayment,
                    'abbr_official' => $abbr_official,
                    'abbr_principal' => $abbr_principal,
                    'code' => $codeNew,
                    'quantity' => $maxQuantity,
                    'amount' => $amount,
                    'status' => $request->michek,
                ]);
                $stock = Stock::where('id_product', $bill_detail->id_product)
                    ->where('id_inventory', $bill_detail->id_inventory)
                    ->latest()
                    ->first();
                if ($stock) {
                    Stock::create([
                        'id_product' => $bill_detail->id_product,
                        'id_user' => auth()->id(),
                        'id_inventory' => $bill_detail->id_inventory,
                        'id_repayment' => $repaymentNew->id,
                        'addition' => $realQuantity,
                        'subtraction' => 0,
                        'quantity' => $stock->quantity + $realQuantity,
                        'description' => 'DEVOLUCION DE MERCANCIA Nº' . $codeNew,
                    ]);
                }

                // DEVOLVER COMPONENTES SI ES INTEGRAL
                if ($product && $product->type == 'INTEGRAL') {
                    $integrals = \App\Models\ProductIntegral::where('id_product', $product->id)->get();
                    foreach ($integrals as $integral) {
                        $componentProduct = Product::find($integral->id_productI);
                        $componentQuantity = $integral->quantity * $maxQuantity;
                        $componentInventory = $bill_detail->id_inventory;

                        $componentStock = Stock::where('id_product', $componentProduct->id)
                            ->where('id_inventory', $componentInventory)
                            ->latest()
                            ->first();
                        if ($componentStock) {
                            Stock::create([
                                'id_product' => $componentProduct->id,
                                'id_user' => auth()->id(),
                                'id_inventory' => $componentInventory,
                                'id_repayment' => $repaymentNew->id,
                                'addition' => $componentQuantity,
                                'subtraction' => 0,
                                'quantity' => $componentStock->quantity + $componentQuantity,
                                'description' => 'DEVOLUCION COMPONENTE INTEGRAL Nº' . $codeNew,
                            ]);
                        }
                        Repayment::create([
                            'id_bill' =>  $bill_detail->id_bill,
                            'id_product' => $componentProduct->id,
                            'id_client' => $bill->id_client,
                            'id_seller' => auth()->id(),
                            'id_inventory' => $componentInventory,
                            'id_currency' => $id_currency,
                            'rate' => $rate,
                            'rate_official' => $rate_official,
                            'abbr_repayment' => $abbr_repayment,
                            'abbr_official' => $abbr_official,
                            'abbr_principal' => $abbr_principal,
                            'code' => $codeNew,
                            'quantity' => $componentQuantity,
                            'amount' => 0,
                            'status' => $request->michek,
                        ]);
                    }
                }
            }
        }
        return response()->json([
            'id' => isset($repaymentNew) ? $repaymentNew->code : null,
            'id_bill' => isset($repaymentNew) ? $repaymentNew->id_bill : null,
            'res' => 'bien'
        ]);
    }
}
