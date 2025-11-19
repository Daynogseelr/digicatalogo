<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductIntegral;
use App\Models\User;
use App\Models\Employee;
use App\Models\Dolar;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Bill_payment;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\Repayment;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\SmallBox;
use App\Models\Stock;
use App\Models\Serial;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Importa la clase Log
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use PhpParser\Node\Stmt\TryCatch;
use Yajra\DataTables\Facades\DataTables;


class BillingController extends Controller
{
    public function indexBilling()
    {
        if (auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $employeeData = Employee::select('smallBox')->where('id_employee', auth()->id())->first(); // Obtener smallBox también
            $employeeSmallBoxEnabled = $employeeData->smallBox ?? 0;
            $inventoryEmployees = auth()->user()->inventoryEmployees;
            $inventoryIds = $inventoryEmployees->pluck('id_inventory');
            $inventories = Inventory::whereIn('id', $inventoryIds)
                ->where('status', 1)
                ->get();
        } else {
            $employeeSmallBoxEnabled = 0;
            $inventories = Inventory::select('id', 'name')->where('status', 1)->get();
        }
        $openSmallBoxModal = false; // Variable para controlar si se abre el modal
        if ($employeeSmallBoxEnabled == 1) {
            // Buscar si existe una caja chica abierta para este empleado
            $smallBox = SmallBox::where('id_employee', auth()->id())
                ->whereNull('id_closure') // Que no esté cerrada (id_closure es NULL)
                ->first();

            if (!$smallBox) {
                // Si no hay una caja chica abierta, entonces se debe abrir el modal
                $openSmallBoxModal = true;
            }
        }
        $clients = User::select('*')->where('type', '!=', 'ADMINISTRADOR')->get();
        $paymentMethods = PaymentMethod::with('currency')->where('status', 1)->get();
        $budget = session('budget');
        if (!$budget) {
            $bill = Bill::with('bill_details')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            if ($bill) {
                if ($bill->bill_details && $bill->bill_details->isNotEmpty()) { // Check if not null and not empty
                    foreach ($bill->bill_details as $bill_detail) {
                        $bill_detail->delete();
                    }
                }
                $bill->delete();
            }
        }
        $id_shopper = session('id_shopper');
        $IVA = session('IVA');
        $id_inventory = session('id_inventory');
        $currencies = Currency::where('status', 1)->get();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        if ($id_shopper) {
            return view('billing.billing', compact('clients', 'id_inventory', 'id_shopper', 'IVA', 'paymentMethods', 'openSmallBoxModal', 'inventories', 'currencies', 'currencyPrincipal', 'currencyOfficial'));
        } else {
            return view('billing.billing', compact('clients', 'paymentMethods', 'openSmallBoxModal', 'inventories', 'currencies', 'currencyPrincipal', 'currencyOfficial'));
        }
    }
   public function ajaxBilling(Request $request)
{
    DB::statement("SET SQL_MODE=''");
    Log::info("--- INICIO AJAX PRODUCT ---");
    Log::info("Request Data: " . json_encode($request->all()));

    try {
        $productsQuery = Product::where('products.status', 1);
        $inventoryId = $request->input('inventory_id');
        // Subconsulta para obtener el último stock por ID
        $productsQuery->leftJoinSub(
            function ($query) use ($inventoryId) {
                $query->from('stocks')
                    ->select('id_product', 'quantity', 'id_inventory', DB::raw('MAX(id) as latest_id'))
                    ->groupBy('id_product', 'id_inventory');

                if ($inventoryId) {
                    $query->where('id_inventory', $inventoryId);
                }
            },
            'latest_stocks',
            function ($join) {
                $join->on('products.id', '=', 'latest_stocks.id_product');
            }
        );
        // LEFT JOIN para obtener la cantidad real del último stock por ID
        $productsQuery->leftJoin('stocks as actual_stock', function ($join) use ($inventoryId) {
            $join->on('products.id', '=', 'actual_stock.id_product')
                ->on('latest_stocks.latest_id', '=', 'actual_stock.id')
                ->on('latest_stocks.id_inventory', '=', 'actual_stock.id_inventory');

            if ($inventoryId) {
                $join->where('actual_stock.id_inventory', $inventoryId);
            }
        });
        $productsQuery->select('products.*', DB::raw('COALESCE(actual_stock.quantity, 0) as stock_orderable'));
        $dataTables = DataTables::of($productsQuery)
            ->addColumn('stock', function ($product) use ($inventoryId) {
                return $product->stock_orderable ?? 'S/S';
            })
            ->addColumn('action', 'billing.billing-action')
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);

        Log::info("--- FIN AJAX PRODUCT (ÉXITO) ---");
        return $dataTables;
    } catch (\Exception $e) {
        Log::error("Error en ajaxProduct: " . $e->getMessage());
        Log::error("Stack Trace: " . $e->getTraceAsString());

        return response()->json([
            'error' => 'Server error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function ajaxBillWait(Request $request)
    {
        $query = DB::table('bills')
            ->join('users as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->leftJoin('bill_payments', 'bill_payments.id_bill', '=', 'bills.id')
            ->select(
                DB::raw('FORMAT(bills.net_amount, 2) as total'),
                'bills.id as id',
                'bills.type as type',
                'bills.payment as payment',
                'bills.created_at as created_at',
                'clients.name as clientName',
                'clients.last_name as clientLast_name',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'sellers.name as sellerName',
                'sellers.last_name as sellerLast_name'
            )
            ->where('bills.status', 2)
            ->groupBy(
                'bills.id',
                'bills.type',
                'bills.payment',
                'bills.created_at',
                'bills.net_amount',  // También las que formateas
                'clients.name',
                'clients.last_name',
                'clients.nationality',
                'clients.ci',
                'sellers.name',
                'sellers.last_name'
            );
        return DataTables::of($query->get())
            ->addColumn('action', 'billing.billWait-action')
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
    public function storeShopper(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|min:2|max:500|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:users,ci',
            ]);
            $existingEmailUser = User::where('email', $request->email)->first();
            if ($existingEmailUser) {
                return back()->withErrors(['email' => 'El correo electrónico ya está registrado.'])->withInput();
            }
            $client = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'nationality' => $request->nationality,
                'ci' => $request->ci,
                'phone' => $request->phone ?: '00000000000', // Use the null coalescing operator
                'email' => $request->email ?: $request->name . '' . $request->last_name . '' . $request->ci,
                'status' => '1',
                'type' => 'COMPRADOR',
                'password' => '123',
                'direction' => $request->direction ?: 'CARUPANO',
            ]);
            $id_shopper = $client->id;
            // Store the ID in the session as flash data
            session()->flash('id_shopper', $id_shopper);
            // Redirect to the indexBilling route
            return redirect()->route('indexBilling');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return redirect()->back()->withErrors($e->validator)->withInput(); // Important: withInput() preserves the form data
        } catch (\Exception $e) {
            // Other errors (e.g., database errors)
            session()->flash('error', 'An error occurred while creating the shopper. Please try again. ' . $e->getMessage()); // Optionally show the error message for debugging (remove in production)
            return redirect()->back()->withInput(); // Preserve the form data
        }
    }
    public function mostrarBill(Request $request)
{
    $bill = Bill::select('*')
        ->where('id_seller', auth()->id())
        ->where('status', 0)
        ->first();
    if (!$bill) {
        return Response()->json(['success' => 'error']);
    }
    $userType = auth()->user()->type;

    $bill_details = Bill_detail::where('id_bill', $bill->id)
        ->get()
        ->map(function ($detail) use ($userType) {
            $product = Product::find($detail->id_product);

            // Inventarios con stock (último por inventario)
            $inventoriesWithStock = \App\Models\Stock::select('id_inventory')
                ->where('id_product', $detail->id_product)
                ->where('quantity', '>', 0)
                ->orderBy('id_inventory')
                ->orderByDesc('id')
                ->get()
                ->unique('id_inventory')
                ->pluck('id_inventory')
                ->toArray();

            // Filtra inventarios autorizados
            if ($userType == 'ADMINISTRADOR' || $userType == 'EMPRESA') {
                $authorizedInventories = $inventoriesWithStock;
            } else {
                $authorizedInventories = DB::table('inventory_employees')
                    ->where('id_employee', auth()->id())
                    ->whereIn('id_inventory', $inventoriesWithStock)
                    ->pluck('id_inventory')
                    ->toArray();
            }

            // Obtiene solo los inventarios autorizados con nombre
            $inventories = Inventory::whereIn('id', $authorizedInventories)
                ->get(['id', 'name'])
                ->map(function($inv) {
                    return [
                        'id' => $inv->id,
                        'name' => $inv->name
                    ];
                })
                ->toArray();

            return [
                'id' => $detail->id,
                'id_product' => $detail->id_product,
                'code' => $detail->code,
                'name' => $detail->name,
                'price' => $detail->price,
                'quantity' => $detail->quantity,
                'discount_percent' => $detail->discount_percent,
                'type' => $product->type ?? 'NORMAL',
                'product_name' => $product->name ?? 'N/A',
                'product_name_fraction' => $product->name_fraction ?? 'N/A',
                'inventories' => $inventories, // Solo autorizados
                'id_inventory' => $detail->id_inventory,
            ];
        });

    return Response()->json([
        'bill_details' => $bill_details,
        'success' => $bill_details->isEmpty() ? 'error' : 'bien'
    ]);
}
    public function addBill(Request $request)
    {
        $product = Product::find($request->id);
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if (!$bill) {
            $bill   =   Bill::create(
                [
                    'id_seller' => auth()->id(),
                    'id_client' => $request->id_client,
                    'code' => 0,
                    'discount_percent' => 0,
                    'total_amount' => 0,
                    'discount' => 0,
                    'net_amount' => 0,
                    'IVA' => $request->billIn,
                    'type' => 'FACTURA',
                    'status' => 0,
                    'payment' => 0,
                ]
            );
        }
        if ($bill->IVA == 1) {
            $price = $product->price + ($product->price * 0.16);
        } else {
            $price = $product->price;
        }
        $bill_detail = Bill_detail::where('id_product', $product->id)
            ->where('id_bill', $bill->id)
            ->first();
        if ($bill_detail) {
            $discount =  $price * ($bill_detail->discount_percent / 100);
            $net_amount =  $price - $discount;
            $bill_detail->quantity = $bill_detail->quantity + 1;
            $bill_detail->total_amount = $bill_detail->total_amount + $price;
            $bill_detail->discount = $bill_detail->discount + $discount;
            $bill_detail->net_amount = $bill_detail->net_amount + $net_amount;
            $bill_detail->save();
        } else {
            $discountpor = Discount::where('id_client', $request->id_client)->first();
            if ($discountpor) {
                $bill->discount_percent = $discountpor->discount;
                $bill->save();
                $discount =  $price * ($discountpor->discount / 100);
                $net_amount =  $price - $discount;
            } else {
                $discount = 0;
                $net_amount =  $price;
            }
            Bill_detail::create(
                [
                    'id_bill' => $bill->id,
                    'id_product' => $request->id,
                    'id_inventory' => $request->id_inventory,
                    'price' => $price,
                    'priceU' => $price,
                    'code' => $product->code,
                    'name' => $product->name,
                    'quantity' => 1,
                    'total_amount' => $price,
                    'discount_percent' =>  $bill->discount_percent,
                    'discount' => $discount,
                    'net_amount' => $net_amount,
                ]
            );
        }
        if ($product->type == 'SERVICIO') {
            foreach ($product->productI as $productI) {
                $product2 = Product::find($productI->id_product);

                // Verifica si el producto integral se debe guardar como fracción
                if (isset($productI->is_fraction) && $productI->is_fraction == 1) {
                    // Si tiene IVA, suma el 16% al precio de fracción
                    if ($bill->IVA == 1) {
                        $price = $product2->price_fraction + ($product2->price_fraction * 0.16);
                    } else {
                        $price = $product2->price_fraction;
                    }
                    $name = $product2->name_fraction;
                    $code = $product2->code_fraction; // Puedes cambiar por otro código si tienes uno especial para fracción
                } else {
                    // Modo completo
                    if ($bill->IVA == 1) {
                        $price = $product2->price + ($product2->price * 0.16);
                    } else {
                        $price = $product2->price;
                    }
                    $name = $product2->name;
                    $code = $product2->code;
                }

                $bill_detail2 = Bill_detail::where('id_product', $product2->id)
                    ->where('id_bill', $bill->id)
                    ->first();

                if ($bill_detail2) {
                    $discount =  $price * ($bill_detail2->discount_percent / 100);
                    $net_amount =  $price - $discount;
                    $bill_detail2->quantity = $bill_detail2->quantity + $productI->quantity;
                    $bill_detail2->total_amount = $bill_detail2->total_amount + $price;
                    $bill_detail2->discount = $bill_detail2->discount + $discount;
                    $bill_detail2->net_amount = $bill_detail2->net_amount + $net_amount;
                    $bill_detail2->name = $name; // Actualiza el nombre según el modo
                    $bill_detail2->price = $price;
                    $bill_detail2->code = $code;
                    $bill_detail2->save();
                } else {
                    $discountpor = Discount::where('id_client', $request->id_client)->first();
                    if ($discountpor) {
                        $bill->discount_percent = $discountpor->discount;
                        $bill->save();
                        $discountU =  $price * ($discountpor->discount / 100);
                        $priceU = $price - $discount;
                        $total_amount = $price * $productI->quantity;
                        $discount = $total_amount * ($bill->discount_percent / 100);
                        $net_amount = $total_amount - $discount;
                    } else {
                        $priceU = $price;
                        $total_amount = $price * $productI->quantity;
                        $discount = 0;
                        $net_amount = $total_amount;
                    }
                    Bill_detail::create([
                        'id_bill' => $bill->id,
                        'id_product' => $product2->id,
                        'id_inventory' => $request->id_inventory,
                        'price' => $price,
                        'priceU' => $priceU,
                        'code' => $code,
                        'name' => $name,
                        'quantity' => $productI->quantity,
                        'total_amount' => $total_amount,
                        'discount_percent' =>  $bill->discount_percent,
                        'discount' => $discount,
                        'net_amount' => $net_amount,
                    ]);
                }
            }
        }
        return Response()->json($bill);
    }
public function addBillCode(Request $request)
{
    $product = Product::where(function ($query) use ($request) {
        $query->where('code', $request->code)
            ->orWhere('code2', $request->code)
            ->orWhere('code3', $request->code)
            ->orWhere('code4', $request->code)
            ->orWhere('code_fraction', $request->code);
    })->first();

    $bill = Bill::select('*')->where('id_seller', auth()->id())
        ->where('status', 0)->first();
    if (!$bill) {
        $bill = Bill::create([
            'id_seller' => auth()->id(),
            'id_client' => $request->id_client,
            'code' => 0,
            'discount_percent' => 0,
            'total_amount' => 0,
            'discount' => 0,
            'net_amount' => 0,
            'IVA' => $request->billIn,
            'type' => 'FACTURA',
            'status' => 0,
            'payment' => 0,
        ]);
    }

    // Determina si la venta es por fracción o por completo
    $isFraction = ($product->code_fraction == $request->code);

    if ($isFraction) {
        // Venta por fracción
        $name = $product->name_fraction;
        $code = $product->code_fraction;
        $price = ($bill->IVA == 1)
            ? $product->price_fraction + ($product->price_fraction * 0.16)
            : $product->price_fraction;
    } else {
        // Venta por completo
        $name = $product->name;
        $code = $product->code;
        $price = ($bill->IVA == 1)
            ? $product->price + ($product->price * 0.16)
            : $product->price;
    }

    // Busca el detalle por id_bill, id_product, nombre y código
    $bill_detail = Bill_detail::where('id_bill', $bill->id)
        ->where('id_product', $product->id)
        ->where('name', $name)
        ->where('code', $code)
        ->first();

    if ($bill_detail) {
        $discount = $price * ($bill_detail->discount_percent / 100);
        $net_amount = $price - $discount;
        $bill_detail->quantity = $bill_detail->quantity + 1;
        $bill_detail->total_amount = $bill_detail->total_amount + $price;
        $bill_detail->discount = $bill_detail->discount + $discount;
        $bill_detail->net_amount = $bill_detail->net_amount + $net_amount;
        $bill_detail->save();
    } else {
        $discountpor = Discount::where('id_client', $request->id_client)->first();
        if ($discountpor) {
            $bill->discount_percent = $discountpor->discount;
            $bill->save();
            $discount = $price * ($discountpor->discount / 100);
            $net_amount = $price - $discount;
        } else {
            $discount = 0;
            $net_amount = $price;
        }
        Bill_detail::create([
            'id_bill' => $bill->id,
            'id_product' => $product->id,
            'id_inventory' => $request->id_inventory,
            'code' => $code,
            'name' => $name,
            'price' => $price,
            'priceU' => $price,
            'quantity' => 1,
            'total_amount' => $price,
            'discount_percent' => $bill->discount_percent,
            'discount' => $discount,
            'net_amount' => $net_amount,
        ]);
    }

    // Si el producto es SERVICIO, sigue igual (puedes adaptar la lógica si necesitas distinguir fracción/completo en integrales)
    if ($product->type == 'SERVICIO') {
        foreach ($product->productI as $productI) {
            $product2 = Product::find($productI->id_product);

            if (isset($productI->is_fraction) && $productI->is_fraction == 1) {
                $price = ($bill->IVA == 1)
                    ? $product2->price_fraction + ($product2->price_fraction * 0.16)
                    : $product2->price_fraction;
                $name = $product2->name_fraction;
                $code = $product2->code_fraction;
            } else {
                $price = ($bill->IVA == 1)
                    ? $product2->price + ($product2->price * 0.16)
                    : $product2->price;
                $name = $product2->name;
                $code = $product2->code;
            }

            $bill_detail2 = Bill_detail::where('id_bill', $bill->id)
                ->where('id_product', $product2->id)
                ->where('name', $name)
                ->where('code', $code)
                ->first();

            if ($bill_detail2) {
                $discount = $price * ($bill_detail2->discount_percent / 100);
                $net_amount = $price - $discount;
                $bill_detail2->quantity = $bill_detail2->quantity + $productI->quantity;
                $bill_detail2->total_amount = $bill_detail2->total_amount + $price;
                $bill_detail2->discount = $bill_detail2->discount + $discount;
                $bill_detail2->net_amount = $bill_detail2->net_amount + $net_amount;
                $bill_detail2->name = $name;
                $bill_detail2->price = $price;
                $bill_detail2->code = $code;
                $bill_detail2->save();
            } else {
                $discountpor = Discount::where('id_client', $request->id_client)->first();
                if ($discountpor) {
                    $bill->discount_percent = $discountpor->discount;
                    $bill->save();
                    $discountU = $price * ($discountpor->discount / 100);
                    $priceU = $price - $discountU;
                    $total_amount = $price * $productI->quantity;
                    $discount = $total_amount * ($bill->discount_percent / 100);
                    $net_amount = $total_amount - $discount;
                } else {
                    $priceU = $price;
                    $total_amount = $price * $productI->quantity;
                    $discount = 0;
                    $net_amount = $total_amount;
                }
                Bill_detail::create([
                    'id_bill' => $bill->id,
                    'id_product' => $product2->id,
                    'id_inventory' => $request->id_inventory,
                    'price' => $price,
                    'priceU' => $priceU,
                    'code' => $code,
                    'name' => $name,
                    'quantity' => $productI->quantity,
                    'total_amount' => $total_amount,
                    'discount_percent' => $bill->discount_percent,
                    'discount' => $discount,
                    'net_amount' => $net_amount,
                ]);
            }
        }
    }
    return Response()->json(1);
}
    public function deleteBillDetail(Request $request)
    {
        $bill_detail = Bill_detail::where('id', $request->id)->delete();
        return Response()->json($bill_detail);
    }
    public function deleteBill()
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())->where('status', 0)->first();
        Bill_detail::where('id_bill', $bill->id)->delete();
        $bill->delete();
        return Response()->json('ELIMINADOS');
    }
    public function changeClient(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if ($bill) {
            $discountpor = Discount::where('id_client', $request->id_client)
                ->first();
            $bill_details  =  Bill_detail::select('*')->where('id_bill', $bill->id)->get();
            if ($discountpor) {
                $bill->update([
                    'id_client' => $request->id_client,
                    'discount_percent' => $discountpor->discount,
                ]);

                $discount = 0;
                $net_amount = 0;
                if ($bill_details) {
                    foreach ($bill_details as $bill_detail) {
                        $discount = $bill_detail->total_amount * ($discountpor->discount / 100);
                        $net_amount = $bill_detail->total_amount - $discount;
                        $bill_detail->discount_percent =  $discountpor->discount;
                        $bill_detail->discount = $discount;
                        $bill_detail->net_amount = $net_amount;
                        $bill_detail->save();
                    }
                }
            } else {
                $bill->update([
                    'id_client' => $request->id_client,
                    'discount_percent' => 0,
                ]);
                if ($bill_details) {
                    foreach ($bill_details as $bill_detail) {
                        $bill_detail->discount_percent = 0;
                        $bill_detail->discount = 0;
                        $bill_detail->net_amount = $bill_detail->total_amount;
                        $bill_detail->save();
                    }
                }
            }
            return Response()->json('bien');
        } else {
            return Response()->json(null);
        }
    }
    public function changeClientVerify(Request $request)
    {
        $user = User::select('status')->find($request->id_client);

        return Response()->json($user->status);
    }
    public function updateQuantity(Request $request)
    {
        $bill_detail = Bill_detail::find($request->id);
        $total_amount = $bill_detail->price * $request->quantity;
        $discount = 0;
        if ($bill_detail->discount_percent != 0) {
            $discount = $total_amount * ($bill_detail->discount_percent / 100);
            $net_amount = $total_amount - $discount;
        } else {
            $net_amount = $total_amount;
        }
        $bill_detail->quantity = $request->quantity;
        $bill_detail->total_amount = $total_amount;
        $bill_detail->discount = $discount;
        $bill_detail->net_amount = $net_amount;
        $bill_detail->save();
        if ($bill_detail->product->type == 'SERVICIO') {
            foreach ($bill_detail->product->productI as $productI) {
                $product2 = Product::find($productI->id_product);
                $bill = Bill::select('*')->where('id_seller', auth()->id())
                    ->where('status', 0)->first();
                $bill_detail2 = Bill_detail::where('id_product', $product2->id)
                    ->where('id_bill', $bill->id)
                    ->first();
                if ($bill_detail2) {
                    $total_amount = $bill_detail2->price * ($request->quantity * $productI->quantity);
                    $discount = 0;
                    if ($bill_detail2->discount_percent != 0) {
                        $discount = $total_amount * ($bill_detail->discount_percent / 100);
                        $net_amount = $total_amount - $discount;
                    } else {
                        $net_amount = $total_amount;
                    }
                    $bill_detail2->quantity = $request->quantity * $productI->quantity;
                    $bill_detail2->total_amount = $total_amount;
                    $bill_detail2->discount = $discount;
                    $bill_detail2->net_amount = $net_amount;
                    $bill_detail2->save();
                }
            }
        }
        return Response()->json($bill_detail);
    }
    public function updateDiscount(Request $request)
    {
        $bill_detail = Bill_detail::find($request->id);
        $discount = 0;
        $bill_detail->discount_percent = $request->discount;
        $bill_detail->save();
        if ($bill_detail->discount_percent != 0) {
            $discount = $bill_detail->total_amount * ($bill_detail->discount_percent / 100);
            $net_amount = $bill_detail->total_amount - $discount;
        } else {
            $net_amount = $bill_detail->total_amount;
        }
        $bill_detail->discount = $discount;
        $bill_detail->net_amount = $net_amount;
        $bill_detail->save();
        return Response()->json($bill_detail);
    }
    public function verificaDiscount(Request $request)
    {
        if (auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $employee = Employee::select('percent')->where('id_employee', auth()->id())->first();
            if ($request->discount <= $employee->percent) {
                return Response()->json(['res' => 'bien', 'discount' => $request->discount, 'id' => $request->id]);
            } else {
                return Response()->json(['res' => 'mal', 'discount' => $employee->percent, 'id' => $request->id]);
            }
        } else {
            return Response()->json(['res' => 'bien', 'discount' => $request->discount, 'id' => $request->id]);
        }
    }
    public function authorizeDiscount(Request $request)
    {
        try {
            $userEmpresa = User::where('type', 'EMPRESA')->first();
            if ($userEmpresa && Hash::check($request->password, $userEmpresa->password)) { // Usa Hash::check
                return response()->json(['res' => 'bien']);
            }
            $employee = Employee::select('percent')->where('id_employee', auth()->id())->first();
            return Response()->json(['res' => 'mal', 'discount' => $employee->percent]);;
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error("Error de base de datos en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Error en la base de datos. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error("Error en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Ocurrió un error inesperado. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        }
    }
    public function facturar(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bill_detailsProducts = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_product', '!=', NULL)
            ->where('id_bill', '=', $bill->id)
            ->first();
        $bill_detailsServices = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_product', NULL)
            ->where('id_bill', '=', $bill->id)
            ->first();
        $repayments = Repayment::select(
            DB::raw('FORMAT(SUM(amount), 2) as amount'),
            'code'
        )
            ->where('id_client', $request->id_client)
            ->where('status', 0)
            ->groupBy('code')
            ->get();
        if ($repayments->isEmpty()) {
            $res = 'notCredit';
        } else {
            $res = 'credit';
        }
        return Response()->json(['repayments' => $repayments, 'res' => $res, 'amountProduct' => round($bill_detailsProducts->net_amount, 2), 'amountService' => round($bill_detailsServices->net_amount, 2)]);
    }
    public function storeBill(Request $request)
    {
        try {
            $bill = Bill::select('*')->where('id_seller', auth()->id())
                ->where('status', 0)->first();
            $bills = Bill::where('type', '!=', 'PRESUPUESTO')
                ->get()
                ->map(function ($bill) {
                    $bill->code = intval(preg_replace('/[^0-9]/', '', $bill->code));
                    return $bill;
                })
                ->max('code');
            if ($bills == NULL) {
                $codeNew = 1;
            } else {
                $codeNew = $bills + 1;
            }
            $bill_details_quantity = Bill_detail::select('id_product', 'id_inventory', 'quantity', 'name')
                ->where('id_bill', '=', $bill->id)
                ->get();
            foreach ($bill_details_quantity as $bill_detail_quantity) {
                if ($bill_detail_quantity->id_product != NULL) {
                    $product = Product::find($bill_detail_quantity->id_product);
                    $bill_detail = Bill_detail::where('id_product', $bill_detail_quantity->id_product)
                        ->where('id_bill', $bill->id)
                        ->first();

                    $subtraction = $bill_detail_quantity->quantity;
                    // Si es fraccionado y el nombre coincide con el nombre de la fracción, descuenta por equivalencia
                    if ($product && $product->type == 'FRACCIONADO' && $bill_detail_quantity->name == $product->name_fraction && $product->equivalence_fraction > 0) {
                        $subtraction = $bill_detail_quantity->quantity / $product->equivalence_fraction;
                    }

                    $stock = Stock::where('id_product', $bill_detail_quantity->id_product)
                        ->where('id_inventory', $bill_detail_quantity->id_inventory)
                        ->latest()
                        ->first();
                    if ($stock) {
                        Stock::create([
                            'id_product' => $bill_detail_quantity->id_product,
                            'id_inventory' => $bill_detail_quantity->id_inventory,
                            'id_user' => auth()->id(),
                            'id_bill' => $bill->id,
                            'addition' => 0,
                            'subtraction' => $subtraction,
                            'quantity' => $stock->quantity - $subtraction,
                            'description' => 'VENTA POR FACTURA Nº' . $codeNew,
                        ]);
                    }

                    // Si el producto es INTEGRAL, descuenta el stock de cada producto integral
                    if ($product && $product->type == 'INTEGRAL') {
                        foreach ($product->productI as $productI) {
                            $stockIntegral = Stock::where('id_product', $productI->id_product)
                                ->where('id_inventory', $bill_detail_quantity->id_inventory)
                                ->latest()
                                ->first();
                            $productIn = Product::find($productI->id_product);
                            if ($stockIntegral) {
                                $subtractionIntegral = $productI->quantity * $bill_detail_quantity->quantity;
                                if ($productI->is_fraction == 1) {
                                    $subtractionIntegral = $subtractionIntegral / $productIn->equivalence_fraction;
                                }
                                Stock::create([
                                    'id_product' => $productI->id_product,
                                    'id_inventory' => $bill_detail_quantity->id_inventory,
                                    'id_user' => auth()->id(),
                                    'id_bill' => $bill->id,
                                    'addition' => 0,
                                    'subtraction' => $subtractionIntegral,
                                    'quantity' => $stockIntegral->quantity - $subtractionIntegral,
                                    'description' => 'VENTA POR FACTURA Nº' . $codeNew . ' (INTEGRAL)',
                                ]);
                            }
                        }
                    }
                }
            }
            foreach ($request->pagos as $pago) {
                if (strpos($pago['metodoId'], 'nota_credito_') === 0) {
                    // Es una nota de crédito
                    $code = str_replace('nota_credito_', '', $pago['metodoId']);

                    // Guarda el pago UNA SOLA VEZ con code_repayment
                    Bill_payment::create([
                        'id_bill' => $bill->id,
                        'id_seller' => auth()->id(),
                        'code_repayment' => $code, // Nuevo campo para el código de la nota de crédito
                        'reference' => $pago['referencia'] ?? null,
                        'amount' => $pago['montoPrincipal'], // Monto en moneda principal
                        'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                        'collection' => 'CONTADO',
                    ]);

                    // Actualiza todos los repayments con ese código a status 1
                    Repayment::where('code', $code)->where('status', 0)->update(['status' => 1]);
                } else {
                    // Pago normal
                    Bill_payment::create([
                        'id_bill' => $bill->id,
                        'id_seller' => auth()->id(),
                        'id_payment_method' => $pago['metodoId'],
                        'reference' => $pago['referencia'] ?? null,
                        'amount' => $pago['montoPrincipal'],
                        'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                        'collection' => 'CONTADO',
                    ]);
                }
            }
            $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount')
                ->where('id_bill', '=', $bill->id)
                ->get();
            $currencyBill = Currency::find($request->id_currency);
            foreach ($bill_detailes as $bill_detail) {
                if ($bill_detail->id_product == null) {
                    $bill_detail->price = $bill_detail->price;
                } else {
                    $bill_detail->price = ($bill_detail->price * $currencyBill->rate2) / $currencyBill->rate;
                }
                $discount_percent = $bill_detail->discount_percent / 100;
                $discount = $bill_detail->price * $discount_percent;
                $bill_detail->priceU = $bill_detail->price - $discount;
                $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
                $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
                $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
                $bill_detail->save();
            }
            $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
                ->where('id_bill', '=', $bill->id)
                ->first();
            $currencyOfficial = Currency::where('is_official', 1)->first();
            $currencyPrincipal = Currency::where('is_principal', 1)->first();

            $bill->id_currency_principal = $currencyPrincipal->id;
            $bill->id_currency_official = $currencyOfficial->id;
            $bill->id_currency_bill = $request->id_currency;
            $bill->rate_bill = $currencyBill->rate;
            $bill->rate_official = $currencyOfficial->rate;
            $bill->abbr_bill = $currencyBill->abbreviation;
            $bill->abbr_official = $currencyOfficial->abbreviation;
            $bill->abbr_principal = $currencyPrincipal->abbreviation;
            $bill->total_amount = $bill_details->total_amount;
            $bill->discount = $bill_details->discount;
            $bill->net_amount = $bill_details->net_amount;
            $bill->code = $codeNew;
            $bill->status = 1;
            $bill->payment = 0;
            $bill->save();

            return Response()->json($bill->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    public function storeBudget(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bills = Bill::where('type', 'PRESUPUESTO')
            ->orderByDesc(DB::raw('CAST(SUBSTRING(code, 2) AS UNSIGNED)'))
            ->first();
        if ($bills) {
            $numberPart = intval(substr($bills->code, 1));
            $codeNew = 'P' . ($numberPart + 1);
        } else {
            $codeNew = 'P1';
        }
        $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount')
            ->where('id_bill', '=', $bill->id)
            ->get();
        $currencyBill = Currency::find($request->id_currency);
        foreach ($bill_detailes as $bill_detail) {
            if ($bill_detail->id_product == null) {
                $bill_detail->price = $bill_detail->price;
            } else {
                $bill_detail->price = ($bill_detail->price * $currencyBill->rate2) / $currencyBill->rate;
            }
            $discount_percent = $bill_detail->discount_percent / 100;
            $discount = $bill_detail->price * $discount_percent;
            $bill_detail->priceU = $bill_detail->price - $discount;
            $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
            $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
            $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
            $bill_detail->save();
        }
        $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        $bill->id_currency_principal = $currencyPrincipal->id;
        $bill->id_currency_official = $currencyOfficial->id;
        $bill->id_currency_bill = $request->id_currency;
        $bill->rate_bill = $currencyBill->rate;
        $bill->rate_official = $currencyOfficial->rate;
        $bill->abbr_bill = $currencyBill->abbreviation;
        $bill->abbr_official = $currencyOfficial->abbreviation;
        $bill->abbr_principal = $currencyPrincipal->abbreviation;
        $bill->total_amount = $bill_details->total_amount;
        $bill->discount = $bill_details->discount;
        $bill->net_amount = $bill_details->net_amount;
        $bill->code = $codeNew;
        $bill->type = 'PRESUPUESTO';
        $bill->status = 1;
        $bill->payment = 0;
        $bill->save();
        return Response()->json($bill->id);
    }
    public function storeCredit(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bills = Bill::where('type', '!=', 'PRESUPUESTO')
            ->get()
            ->map(function ($bill) {
                $bill->code = intval(preg_replace('/[^0-9]/', '', $bill->code));
                return $bill;
            })
            ->max('code');
        if ($bills == NULL) {
            $codeNew = 1;
        } else {
            $codeNew = $bills + 1;
        }
        $bill_details_quantity = Bill_detail::select('id_product', 'id_inventory', 'quantity')
            ->where('id_bill', '=', $bill->id)
            ->get();
        foreach ($bill_details_quantity as $bill_detail_quantity) {
            if ($bill_detail_quantity->id_product != NULL) {
                $product = Product::find($bill_detail_quantity->id_product);
                $bill_detail = Bill_detail::where('id_product', $bill_detail_quantity->id_product)
                    ->where('id_bill', $bill->id)
                    ->first();

                $subtraction = $bill_detail_quantity->quantity;
                // Si es fraccionado y el nombre coincide con el nombre de la fracción, descuenta por equivalencia
                if ($product && $product->type == 'FRACCIONADO' && $bill_detail_quantity->name == $product->name_fraction && $product->equivalence_fraction > 0) {
                    $subtraction = $bill_detail_quantity->quantity / $product->equivalence_fraction;
                }

                $stock = Stock::where('id_product', $bill_detail_quantity->id_product)
                    ->where('id_inventory', $bill_detail_quantity->id_inventory)
                    ->latest()
                    ->first();
                if ($stock) {
                    Stock::create([
                        'id_product' => $bill_detail_quantity->id_product,
                        'id_inventory' => $bill_detail_quantity->id_inventory,
                        'id_user' => auth()->id(),
                        'id_bill' => $bill->id,
                        'addition' => 0,
                        'subtraction' => $subtraction,
                        'quantity' => $stock->quantity - $subtraction,
                        'description' => 'VENTA POR FACTURA Nº' . $codeNew,
                    ]);
                }

                // Si el producto es INTEGRAL, descuenta el stock de cada producto integral
                if ($product && $product->type == 'INTEGRAL') {
                    foreach ($product->productI as $productI) {
                        $stockIntegral = Stock::where('id_product', $productI->id_product)
                            ->where('id_inventory', $bill_detail_quantity->id_inventory)
                            ->latest()
                            ->first();
                        $productIn = Product::find($productI->id_product);
                        if ($stockIntegral) {
                            $subtractionIntegral = $productI->quantity * $bill_detail_quantity->quantity;
                            if ($productI->is_fraction == 1) {
                                $subtractionIntegral = $subtractionIntegral / $productIn->equivalence_fraction;
                            }
                            Stock::create([
                                'id_product' => $productI->id_product,
                                'id_inventory' => $bill_detail_quantity->id_inventory,
                                'id_user' => auth()->id(),
                                'id_bill' => $bill->id,
                                'addition' => 0,
                                'subtraction' => $subtractionIntegral,
                                'quantity' => $stockIntegral->quantity - $subtractionIntegral,
                                'description' => 'VENTA POR FACTURA Nº' . $codeNew . ' (INTEGRAL)',
                            ]);
                        }
                    }
                }
            }
        }
        $bill_detailes = Bill_detail::select('id', 'id_product', 'quantity', 'price', 'priceU', 'discount_percent', 'discount', 'net_amount', 'total_amount')
            ->where('id_bill', '=', $bill->id)
            ->get();
        $currencyBill = Currency::find($request->id_currency);
        foreach ($bill_detailes as $bill_detail) {
            if ($bill_detail->id_product == null) {
                $bill_detail->price = $bill_detail->price;
            } else {
                $bill_detail->price = ($bill_detail->price * $currencyBill->rate2) / $currencyBill->rate;
            };
            $discount_percent = $bill_detail->discount_percent / 100;
            $discount = $bill_detail->price * $discount_percent;
            $bill_detail->priceU = $bill_detail->price - $discount;
            $bill_detail->total_amount = $bill_detail->price * $bill_detail->quantity;
            $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
            $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
            $bill_detail->save();
        }
        $bill_details = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $currencyOfficial = Currency::where('is_official', 1)->first();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();

        $bill->id_currency_principal = $currencyPrincipal->id;
        $bill->id_currency_official = $currencyOfficial->id;
        $bill->id_currency_bill = $request->id_currency;
        $bill->rate_bill = $currencyBill->rate;
        $bill->rate_official = $currencyOfficial->rate;
        $bill->abbr_bill = $currencyBill->abbreviation;
        $bill->abbr_official = $currencyOfficial->abbreviation;
        $bill->abbr_principal = $currencyPrincipal->abbreviation;
        $bill->total_amount = $bill_details->total_amount;
        $bill->discount = $bill_details->discount;
        $bill->net_amount = $bill_details->net_amount;
        $bill->code = $codeNew;
        $bill->type = 'CREDITO';
        $bill->creditDays = $request->creditDays;
        $bill->status = 1;
        $bill->payment = $bill_details->net_amount;
        $bill->save();
        return Response()->json($bill->id);
    }

    public function budget(Request $request)
    {
        if (strpos($request->code, "T") === 0) {
            $service = Service::select('*')->where('ticker', $request->code)->where('status', 'TERMINADO')->first();
            if ($service) {
                $id_shopper = $service->id_client;
                $bill = Bill::select('id', 'IVA')->where('id_seller', auth()->id())
                    ->where('status', 0)->first();
                if ($bill) {
                    Bill_detail::where('id_bill', '=', $bill->id)->delete();
                    $bill->id_client = $service->id_client;
                    $bill->IVA = 0;
                    $bill->save();
                } else {
                    $bill   =   Bill::create(
                        [
                            'id_seller' => auth()->id(),
                            'id_client' => $service->id_client,
                            'code' => 0,
                            'discount_percent' => 0,
                            'total_amount' => 0,
                            'discount' => 0,
                            'net_amount' => 0,
                            'IVA' => 0,
                            'type' => 'FACTURA',
                            'status' => 0,
                            'payment' => 0,
                        ]
                    );
                }
                foreach ($service->serviceDetails as $serviceDetail) {
                    $productName = $serviceDetail->procedure; // Valor por defecto

                    if ($serviceDetail->id_product !== null) {
                        $product = Product::find($serviceDetail->id_product);
                        // Si se encuentra el producto, usamos su nombre    
                        $productName = $product->name;
                        $price = $product->price;
                        if ($serviceDetail->mode == 'FRACCION') {
                            $productName = $product->name_fraction;
                            $price = $product->price_fraction;
                        }
                        Bill_detail::create([
                            'id_bill' => $bill->id,
                            'id_product' => $serviceDetail->id_product,
                            'id_inventory' => $request->id_inventory,
                            'code' => $serviceDetail->id_product,
                            'name' => $productName, // Usamos el nombre obtenido
                            'price' => $price,
                            'priceU' => $price,
                            'quantity' => $serviceDetail->quantity,
                            'total_amount' => $price * $serviceDetail->quantity,
                            'discount_percent' => 0,
                            'discount' => 0,
                            'net_amount' =>  $price * $serviceDetail->quantity,
                        ]);
                    } else {
                        Bill_detail::create([
                            'id_bill' => $bill->id,
                            'id_product' => $serviceDetail->id_product,
                            'id_inventory' => $request->id_inventory,
                            'code' => '000',
                            'name' => $productName, // Usamos el nombre obtenido
                            'price' => $serviceDetail->priceU,
                            'priceU' => $serviceDetail->priceU,
                            'quantity' => $serviceDetail->quantity,
                            'total_amount' => $serviceDetail->price,
                            'discount_percent' => 0,
                            'discount' => 0,
                            'net_amount' => $serviceDetail->price,
                        ]);
                    }
                }
                $id_shopper = $service->id_client;
            } else {
                $id_shopper = 0;
            }
            // Aquí el código que se ejecuta si la condición es verdadera
        } else {
            $billOld = Bill::select('*')->where('code', $request->code)->first();
            if ($billOld) {
                $bill = Bill::select('id', 'IVA')->where('id_seller', auth()->id())
                    ->where('status', 0)->first();
                if ($bill) {
                    $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
                    $bill->id_client = $billOld->id_client;
                    $bill->IVA = $billOld->IVA;
                    $bill->save();
                } else {
                    if (!$bill) {
                        $bill   =   Bill::create(
                            [
                                'id_seller' => auth()->id(),
                                'id_client' => $billOld->id_client,
                                'code' => 0,
                                'discount_percent' => 0,
                                'total_amount' => 0,
                                'discount' => 0,
                                'net_amount' => 0,
                                'IVA' => $billOld->IVA,
                                'type' => 'FACTURA',
                                'status' => 0,
                                'payment' => 0,
                            ]
                        );
                    }
                }
                $bill_detailsOld = Bill_detail::select('*')
                    ->where('id_bill', '=', $billOld->id)
                    ->get();
                foreach ($bill_detailsOld as $bill_detail) {
                    $product = Product::find($bill_detail->id_product);
                    if ($product) {
                        if ($bill->IVA == 1) {
                            $price = $product->price + ($product->price * 0.16);
                        } else {
                            $price = $product->price;
                        }
                        if ($product->type == 'FRACCIONADO' && $product->name_fraction == $bill_detail->name) {
                            if ($bill->IVA == 1) {
                                $price =$product->price_fraction + ($product->price_fraction * 0.16);
                            } else {
                                $price =$product->price_fraction;
                            }
                        }
                    } else {
                        $price = $bill_detail->price;
                    }
                    $discount_percent = $bill_detail->discount_percent / 100;
                    $discount = $price * $discount_percent;
                    $bill_detail->priceU = $price - $discount;
                    $bill_detail->total_amount = $price * $bill_detail->quantity;
                    $bill_detail->discount = $bill_detail->total_amount * $discount_percent; // Calculate discount based on total_amount
                    $bill_detail->net_amount = $bill_detail->total_amount - $bill_detail->discount;
                    Bill_detail::create(
                        [
                            'id_bill' => $bill->id,
                            'id_product' => $bill_detail->id_product,
                            'id_inventory' => $request->id_inventory,
                            'code' => $bill_detail->code,
                            'name' => $bill_detail->name,
                            'price' => $price,
                            'priceU' => $bill_detail->priceU,
                            'quantity' => $bill_detail->quantity,
                            'total_amount' => $bill_detail->total_amount,
                            'discount_percent' => $bill_detail->discount_percent,
                            'discount' => $bill_detail->discount,
                            'net_amount' => $bill_detail->net_amount,
                        ]
                    );
                }
                $id_shopper = $billOld->id_client;
            } else {
                $bill = Bill::select('id', 'IVA')->where('id_seller', auth()->id())
                    ->where('status', 0)->first();
                if ($bill) {
                    $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
                }
                $id_shopper = 0;
            }
        }
        session()->flash('id_shopper', $id_shopper);
        session()->flash('budget', 'budget');
        session()->flash('IVA', $bill->IVA ?? 0);
        session()->flash('id_inventory', $request->id_inventory ?? 0);
        // Redirect to the indexBilling route
        return redirect()->route('indexBilling');
    }
    public function changeNoteCredit(Request $request)
    {
        $repayments = Repayment::select('*')->where('id_client', $request->id_client)->where('status', 0)->first();
        if ($repayments) {
            $res = 'credit';
        } else {
            $res = 'notCredit';
        }
        return Response()->json($res);
    }

    public function changeBillIn(Request $request)
    {
        $bill = Bill::where('id_seller', auth()->id())
            ->where('status', 0)
            ->first();

        if (!$bill) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }
        try {
            DB::beginTransaction(); // Inicia la transacción
            $bill_details = Bill_detail::where('id_bill', $bill->id)->get();
            if ($bill_details->isEmpty()) {
                return response()->json(['message' => 'No hay detalles para esta factura'], 200);
            }
            if ($request->billIn == 1) {
                if ($bill->IVA == 0) {
                    foreach ($bill_details as $bill_detail) {
                        $bill_detail->price = $bill_detail->price + ($bill_detail->price * 0.16);
                        $bill_detail->priceU = $bill_detail->priceU + ($bill_detail->priceU * 0.16);
                        $bill_detail->total_amount = $bill_detail->total_amount + ($bill_detail->total_amount * 0.16);
                        $bill_detail->discount = $bill_detail->discount + ($bill_detail->discount * 0.16);
                        $bill_detail->net_amount = $bill_detail->net_amount + ($bill_detail->net_amount * 0.16);
                        $bill_detail->save();
                    }
                }
            } else {
                if ($bill->IVA == 1) {
                    foreach ($bill_details as $bill_detail) {
                        $bill_detail->price = $bill_detail->price / 1.16;
                        $bill_detail->priceU = $bill_detail->priceU / 1.16;
                        $bill_detail->total_amount = $bill_detail->total_amount / 1.16;
                        $bill_detail->discount = $bill_detail->discount / 1.16;
                        $bill_detail->net_amount = $bill_detail->net_amount / 1.16;
                        $bill_detail->save();
                    }
                }
            }
            $bill->update([
                'IVA' => $request->billIn,
            ]);
            DB::commit(); // Confirma la transacción

            return response()->json(['message' => 'Factura y detalles actualizados correctamente', 'bill_id' => $bill->id], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte la transacción en caso de error
            return response()->json(['error' => 'Error al actualizar la factura: ' . $e->getMessage()], 500); // Devuelve el mensaje de error para debugging
        }
    }
    public function verifyStock(Request $request)
    {
        $product = Product::find($request->id_product);
        $bill = Bill::where('id_seller', auth()->id())
            ->where('status', 0)
            ->first();

        // Busca todos los inventarios donde el producto tiene stock > 0
        $inventoriesWithStock = Stock::where('id_product', $product->id)
            ->where('quantity', '>', 0)
            ->pluck('id_inventory')
            ->toArray();

        $userType = auth()->user()->type;
        if ($userType == 'ADMINISTRADOR' || $userType == 'EMPRESA') {
            $authorizedInventories = $inventoriesWithStock;
        } else {
            $authorizedInventories = DB::table('inventory_employees')
                ->where('id_employee', auth()->id())
                ->whereIn('id_inventory', $inventoriesWithStock)
                ->pluck('id_inventory')
                ->toArray();
        }

        if (empty($authorizedInventories)) {
            return response()->json([
                'res' => 'mal',
                'id_product' => $product->id,
                'code' => $product->code,
                'inventories' => [],
            ]);
        }

        $defaultInventory = min($authorizedInventories);

        if ($product->type == 'SERVICIO' || $product->type == 'INTEGRAL') {
            // Verifica stock en cada inventario autorizado
            foreach ($authorizedInventories as $id_inventory) {
                $res = $this->verifyServicioProductStock($product, $bill, $request->id_product, $id_inventory);
                if ($res->getData()->res == 'bien') {
                    return response()->json([
                        'res' => 'bien',
                        'id_product' => $product->id,
                        'code' => $product->code,
                        'inventories' => $authorizedInventories,
                        'default_inventory' => $id_inventory,
                    ]);
                }
            }
            // Si ninguno tiene stock suficiente, incluir igualmente el default_inventory (el más antiguo)
            return response()->json([
                'res' => 'mal',
                'id_product' => $product->id,
                'code' => $product->code,
                'inventories' => $authorizedInventories,
                'default_inventory' => $defaultInventory,
            ]);
        } elseif ($product->type == 'FRACCIONADO') {
            foreach ($authorizedInventories as $id_inventory) {
                $res = $this->verifyFractionProductStock($product, $bill, $request->id_product, $id_inventory);
                if ($res->getData()->res == 'bien') {
                    return response()->json([
                        'res' => 'bien',
                        'id_product' => $product->id,
                        'code' => $product->code,
                        'inventories' => $authorizedInventories,
                        'default_inventory' => $id_inventory,
                    ]);
                }
            }
            return response()->json([
                'res' => 'mal',
                'id_product' => $product->id,
                'code' => $product->code,
                'inventories' => $authorizedInventories,
                'default_inventory' => $defaultInventory,
            ]);
        } else {
            foreach ($authorizedInventories as $id_inventory) {
                $res = $this->verifySimpleProductStock($product, $bill, $request->id_product, $id_inventory);
                if ($res->getData()->res == 'bien') {
                    return response()->json([
                        'res' => 'bien',
                        'id_product' => $product->id,
                        'code' => $product->code,
                        'inventories' => $authorizedInventories,
                        'default_inventory' => $id_inventory,
                    ]);
                }
            }
            return response()->json([
                'res' => 'mal',
                'id_product' => $product->id,
                'code' => $product->code,
                'inventories' => $authorizedInventories,
                'default_inventory' => $defaultInventory,
            ]);
        }

        // Elige el inventario predeterminado (el más antiguo)
        $defaultInventory = min($authorizedInventories);

        return response()->json([
            'res' => 'bien',
            'id_product' => $product->id,
            'code' => $product->code,
            'inventories' => $authorizedInventories,
            'default_inventory' => $defaultInventory,
        ]);
    }
    private function verifyServicioProductStock($product, $bill, $productId, $id_inventory)
    {
        if ($product->stock == 1) {
            $stock = Stock::where('id_product', $productId)
                ->where('id_inventory', $id_inventory)
                ->latest()
                ->first();
            if (!$stock) {
                return response()->json(['res' => 'stock', 'id_product' => $productId, 'code' => $product->code]);
            }
            $availableStock = $stock->quantity;
            if ($bill) {
                $bill_detail = Bill_detail::where('id_product', $product->id)
                    ->where('id_bill', $bill->id)
                    ->first();
                $availableStock -= $bill_detail ? $bill_detail->quantity : 0;
            }
            if ($availableStock <= 0) {
                return response()->json(['res' => 'mal', 'id_product' => $productId, 'code' => $product->code]);
            }
            foreach ($product->productI as $productI) {
                $product2 = Product::find($productI->id_product);
                if ($product2->stock == 1) {
                    $stock2 = Stock::where('id_product', $productI->id_product)
                        ->where('id_inventory', $id_inventory)
                        ->latest()
                        ->first();
                    if (!$stock2) {
                        return response()->json(['res' => 'stock', 'id_product' => $productId, 'code' => $product->code]);
                    }
                    $requiredQuantity = $productI->quantity;
                    if ($bill) {
                        $bill_detail2 = Bill_detail::where('id_product', $product2->id)
                            ->where('id_bill', $bill->id)
                            ->first();
                        $requiredQuantity += $bill_detail2 ? $bill_detail2->quantity : 0;
                    }
                    if ($stock2->quantity < $requiredQuantity) {
                        return response()->json(['res' => 'mal', 'id_product' => $productId, 'code' => $product->code]);
                    }
                }
            }
            return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
        } else {
            foreach ($product->productI as $productI) {
                $product2 = Product::find($productI->id_product);
                if ($product2->stock == 1) {
                    $stock2 = Stock::where('id_product', $productI->id_product)
                        ->where('id_inventory', $id_inventory)
                        ->latest()
                        ->first();
                    if (!$stock2) {
                        return response()->json(['res' => 'stock', 'id_product' => $productId, 'code' => $product->code]);
                    }
                    $requiredQuantity = $productI->quantity;
                    if ($bill) {
                        $bill_detail2 = Bill_detail::where('id_product', $product2->id)
                            ->where('id_bill', $bill->id)
                            ->first();
                        $requiredQuantity += $bill_detail2 ? $bill_detail2->quantity : 0;
                    }
                    if ($stock2->quantity < $requiredQuantity) {
                        return response()->json(['res' => 'mal', 'id_product' => $productId, 'code' => $product->code]);
                    }
                }
            }
            return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
        }
    }
    private function verifySimpleProductStock($product, $bill, $productId, $id_inventory)
    {
        if ($product->stock == 1) {
            $stock = Stock::where('id_product', $productId)
                ->where('id_inventory', $id_inventory)
                ->latest()
                ->first();
            if (!$stock) {
                return response()->json(['res' => 'stock', 'id_product' => $productId, 'code' => $product->code]);
            }
            $availableStock = $stock->quantity;
            if ($bill) {
                $bill_detail = Bill_detail::where('id_product', $product->id)
                    ->where('id_bill', $bill->id)
                    ->first();
                $availableStock -= $bill_detail ? $bill_detail->quantity : 0;
            }
            if ($availableStock > 0) {
                return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
            } else {
                return response()->json(['res' => 'mal', 'id_product' => $productId, 'code' => $product->code]);
            }
        } else {
            return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
        }
    }
    private function verifyFractionProductStock($product, $bill, $productId, $id_inventory)
    {
        // Verifica si el producto maneja stock
        if ($product->stock == 1) {
            $stock = Stock::where('id_product', $productId)
                ->where('id_inventory', $id_inventory)
                ->latest()
                ->first();
            if (!$stock) {
                return response()->json(['res' => 'stock', 'id_product' => $productId, 'code' => $product->code]);
            }
            $availableStock = $stock->quantity;

            // Verifica si ya hay detalles en la factura y si es por fracción o completo
            $bill_detail = Bill_detail::where('id_product', $product->id)
                ->where('id_bill', $bill ? $bill->id : null)
                ->first();

            // Si el detalle se está vendiendo por fracción, la cantidad en stock debe ser suficiente para la equivalencia
            if ($bill_detail && $bill_detail->name == $product->name_fraction && $product->equivalence_fraction > 0) {
                $required = $bill_detail->quantity / $product->equivalence_fraction;
            } else {
                $required = $bill_detail ? $bill_detail->quantity : 0;
            }

            $availableStock -= $required;

            if ($availableStock <= 0) {
                return response()->json(['res' => 'mal', 'id_product' => $productId, 'code' => $product->code]);
            }
            return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
        } else {
            return response()->json(['res' => 'bien', 'id_product' => $productId, 'code' => $product->code]);
        }
    }
    public function verifyStockCode(Request $request)
{
    $product = Product::where(function ($query) use ($request) {
        $query->where('code', $request->code)
            ->orWhere('code2', $request->code)
            ->orWhere('code3', $request->code)
            ->orWhere('code4', $request->code)
            ->orWhere('code_fraction', $request->code);
    })->first();
    if (!$product) {
        return response()->json(['res' => 'noCode']);
    }
    $bill = Bill::where('id_seller', auth()->id())
        ->where('status', 0)
        ->first();

    // Busca todos los inventarios donde el producto tiene stock > 0
    $inventoriesWithStock = Stock::where('id_product', $product->id)
        ->where('quantity', '>', 0)
        ->pluck('id_inventory')
        ->toArray();

    $userType = auth()->user()->type;
    if ($userType == 'ADMINISTRADOR' || $userType == 'EMPRESA') {
        $authorizedInventories = $inventoriesWithStock;
    } else {
        $authorizedInventories = DB::table('inventory_employees')
            ->where('id_employee', auth()->id())
            ->whereIn('id_inventory', $inventoriesWithStock)
            ->pluck('id_inventory')
            ->toArray();
    }

    if (empty($authorizedInventories)) {
        return response()->json([
            'res' => 'mal',
            'code' => $product->code,
            'inventories' => [],
        ]);
    }

    $defaultInventory = min($authorizedInventories);

        if ($product->type == 'SERVICIO' || $product->type == 'INTEGRAL') {
        foreach ($authorizedInventories as $id_inventory) {
            $res = $this->verifyServicioProductStock($product, $bill, $product->id, $id_inventory);
            if ($res->getData()->res == 'bien') {
                return response()->json([
                    'res' => 'bien',
                    'code' => $product->code,
                    'inventories' => $authorizedInventories,
                    'default_inventory' => $id_inventory,
                ]);
            }
        }
            return response()->json([
                'res' => 'mal',
                'code' => $product->code,
                'inventories' => $authorizedInventories,
                'default_inventory' => $defaultInventory,
            ]);
    } elseif ($product->type == 'FRACCIONADO') {
        $code = $product->code;
        if ($product->code_fraction == $request->code) {
            $code = $product->code_fraction;
            
        }
        foreach ($authorizedInventories as $id_inventory) {
            $res = $this->verifyFractionProductStock($product, $bill, $product->id, $id_inventory);
            if ($res->getData()->res == 'bien') {
                
                return response()->json([
                    'res' => 'bien',
                    'code' => $code,
                    'inventories' => $authorizedInventories,
                    'default_inventory' => $id_inventory,
                ]);
            }
        }
        return response()->json([
            'res' => 'mal',
            'code' => $code,
            'inventories' => $authorizedInventories,
            'default_inventory' => $defaultInventory,
        ]);
    } else {
        foreach ($authorizedInventories as $id_inventory) {
            $res = $this->verifySimpleProductStock($product, $bill, $product->id, $id_inventory);
            if ($res->getData()->res == 'bien') {
                return response()->json([
                    'res' => 'bien',
                    'code' => $product->code,
                    'inventories' => $authorizedInventories,
                    'default_inventory' => $id_inventory,
                ]);
            }
        }
        return response()->json([
            'res' => 'mal',
            'code' => $product->code,
            'inventories' => $authorizedInventories,
            'default_inventory' => $defaultInventory,
        ]);
    }
}
    public function verifyStockQuantity(Request $request)
    {
        $product = Product::find($request->id_product);
        if ($product->type == 'SERVICIO') {
            if ($product->stock == 1) {
                $stock = Stock::where('id_product', $request->id_product)
                    ->where('id_inventory',  $request->id_inventory)
                    ->latest()
                    ->first();
                if ($stock->quantity >= $request->quantity) {
                    foreach ($product->productI as $productI) {
                        $product2 = Product::find($productI->id_product);
                        if ($product2->stock == 1) {
                            $stock2 = Stock::where('id_product', $productI->id_product)
                                ->where('id_inventory',  $request->id_inventory)
                                ->latest()
                                ->first();
                            $quantity = $productI->quantity * $request->quantity;
                            if ($stock2->quantity < $quantity) {
                                return response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity]);
                            }
                        }
                    }
                    return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
                } else {
                    return response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity]);
                }
            } else {
                foreach ($product->productI as $productI) {
                    $product2 = Product::find($productI->id_product);
                    if ($product2->stock == 1) {
                        $stock2 = Stock::where('id_product', $productI->id_product)
                            ->where('id_inventory',  $request->id_inventory)
                            ->latest()
                            ->first();
                        $quantity = $productI->quantity * $request->quantity;
                        if ($stock2->quantity < $quantity) {
                            return response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity]);
                        }
                    }
                }
                return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
            }
        } elseif ($product->type == 'FRACCIONADO') {
            if ($product->stock == 1) {
                $stock = Stock::where('id_product', $request->id_product)
                    ->where('id_inventory',  $request->id_inventory)
                    ->latest()
                    ->first();
                // Si la venta es por fracción, la cantidad real a descontar es cantidad / equivalencia
                $bill_detail = Bill_detail::where('id_product', $product->id)
                    ->where('id_bill', Bill::where('id_seller', auth()->id())->where('status', 0)->value('id'))
                    ->first();

                $equivalence = ($product->equivalence_fraction > 0) ? $product->equivalence_fraction : 1;
                $required = $request->quantity;
                if ($bill_detail && $bill_detail->name == $product->name_fraction) {
                    $required = $request->quantity / $equivalence;
                }

                if ($stock->quantity >= $required) {
                    return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
                } else {
                    return Response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity, 'quantityMax' => $stock->quantity]);
                }
            }
            return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
        } else {
            if ($product->stock == 1) {
                $stock = Stock::where('id_product', $request->id_product)
                    ->where('id_inventory',  $request->id_inventory)
                    ->latest()
                    ->first();
                if ($stock->quantity >= $request->quantity) {
                    return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
                } else {
                    return Response()->json(['res' => 'mal', 'id' => $request->id, 'quantity' => $request->quantity, 'quantityMax' => $stock->quantity]);
                }
            }
            return Response()->json(['res' => 'bien', 'id' => $request->id, 'quantity' => $request->quantity]);
        }
    }
    public function authorize(Request $request)
    {
        try {
            $userSupervisor = DB::table('users')
                ->join('employees', 'employees.id_employee', '=', 'users.id')
                ->where('users.type', 'SUPERVISOR')
                ->get();
            if ($userSupervisor) {
                foreach ($userSupervisor as $supervisor) {
                    if ($supervisor && Hash::check($request->password, $supervisor->password)) { // Usa Hash::check
                        return response()->json(['res' => 'bien', 'por' => $request->por, 'valor' => $request->valor, 'quantity' => $request->quantity, 'user_type' => 'SUPERVISOR', 'user_id' => $supervisor->id]);
                    }
                }
            }
            $userAdministrativo = DB::table('users')
                ->join('employees', 'employees.id_employee', '=', 'users.id')
                ->where('users.type', 'ADMINISTRATIVO')
                ->get();
            if ($userAdministrativo) {
                foreach ($userAdministrativo as $administrativo) {
                    if ($administrativo && Hash::check($request->password, $administrativo->password)) { // Usa Hash::check
                        return response()->json(['res' => 'bien', 'por' => $request->por, 'valor' => $request->valor, 'quantity' => $request->quantity, 'user_type' => 'SUPERVISOR', 'user_id' => $administrativo->id]);
                    }
                }
            }
            $userEmpresa = DB::table('users')
                ->where('type', 'EMPRESA')
                ->first();
            if ($userEmpresa && Hash::check($request->password, $userEmpresa->password)) { // Usa Hash::check
                return response()->json(['res' => 'bien', 'por' => $request->por, 'valor' => $request->valor, 'quantity' => $request->quantity, 'user_type' => 'EMPRESA', 'user_id' => $userEmpresa->id]);
            }
            return response()->json(['res' => 'mal', 'message' => 'Credenciales incorrectas.']);
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            Log::error("Error de base de datos en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Error en la base de datos. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Manejo de otros errores
            Log::error("Error en autorización: " . $e->getMessage());
            return response()->json(['res' => 'mal', 'message' => 'Ocurrió un error inesperado. Por favor, contacte al administrador.', 'error' => $e->getMessage()]);
        }
    }
    public function verifySerial()
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $productSerial = [];
        if ($bill) { // Check if a bill is found
            foreach ($bill->bill_details as $bill_detail) {
                if ($bill_detail->product && $bill_detail->product->serial == 1) { // Check if product exists
                    $productSerial[] = [
                        'code' => $bill_detail->product->code,
                        'name' => $bill_detail->product->name,
                        'quantity' => $bill_detail->quantity, // Access the quantity from bill_details
                        'id_bill_detail' => $bill_detail->id, // Optionally include the bill_detail ID
                        'id_product' => $bill_detail->product->id, // Optionally include the product ID
                    ];
                }
            }
        }
        if (empty($productSerial)) {
            return response()->json(['res' => 'sinSerial']);
        } else {
            return response()->json(['res' => 'conSerial', 'productSerial' => $productSerial]);
        }
    }
    public function saveSerials(Request $request)
    {
        foreach ($request->serials as $serialData) {
            $billDetail = Bill_detail::find($serialData['bill_detail_id']);
            if ($billDetail) { // Verifica si se encontró el BillDetail 
                $billDetail->name = $billDetail->name . ' - ' . $serialData['serial']; // Agrega los seriales al nombre
                $billDetail->save();
                $serial = Serial::where('id_product', $billDetail->id_product)->where('serial', $serialData['serial'])->first();
                if ($serial) {
                    $serial->status = 1;
                    $serial->save();
                }
            } else {
                // Manejar el caso en que no se encuentra el BillDetail
                Log::error("No se encontró el BillDetail con ID: " . $serialData['bill_detail_id']);
                return response()->json(['error' => 'No se encontró el BillDetail'], 500); // Devuelve un error 500
            }
        }
        return response()->json('bien');
    }
    public function changeClientCredit(Request $request)
    {
        $billSUM = Bill::selectRaw('SUM(payment) as payment')
            ->where('id_client', $request->id_client)
            ->where('payment', '!=', 0)
            ->whereRaw('DATE_ADD(created_at, INTERVAL creditDays DAY) < NOW()')
            ->first();
        if ($billSUM->payment > 0) {
            return Response()->json(['res' => 'credit', 'billSUM' => $billSUM->payment]);
        } else {
            return Response()->json(['res' => 'nocredit',]);
        }
    }
    public function storeBillWait(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        $bill_details = Bill_detail::selectRaw('SUM(net_amount) as net_amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        $net_amount = $bill_details->net_amount;

        $bill->type = 'ESPERA';
        $bill->status = 2;
        $bill->net_amount = $net_amount;
        $bill->save();
        return Response()->json($bill->id);
    }
    public function billWaitStore(Request $request)
    {
        $bill = Bill::select('id')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if ($bill) {
            $bill_details = Bill_detail::where('id_bill', '=', $bill->id)->delete();
            $bill = Bill::where('id_seller', auth()->id())
                ->where('status', 0)->delete();
        }
        $billWait = Bill::find($request->id_billWait);
        $billWait->id_seller = auth()->id();
        $billWait->net_amount = 0;
        $billWait->type = 'FACTURA';
        $billWait->status = 0;
        $billWait->save();
        session()->flash('id_shopper', $billWait->id_client);
        session()->flash('IVA', $billWait->IVA);
        session()->flash('budget', 'budget');
        // Redirect to the indexBilling route
        return redirect()->route('indexBilling');
    }
    public function storeSmallBox(Request $request)
    {
        $request->validate([
            'small_boxes' => 'required|array|min:1',
            'small_boxes.*.id_currency' => 'required|exists:currencies,id',
            'small_boxes.*.cash' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->small_boxes as $box) {
            SmallBox::create([
                'id_employee' => auth()->id(),
                'id_currency' => $box['id_currency'],
                'cash' => $box['cash'],
            ]);
        }

        return redirect()->route('indexBilling')->with('success2', 'Caja chica abierta con éxito.');
    }
    public function changeBillingInventory(Request $request)
    {
        $bill = Bill::select('*')->where('id_seller', auth()->id())
            ->where('status', 0)->first();
        if ($bill) { // Siempre es buena práctica verificar si se encontró la factura
            foreach ($bill->bill_details as $detail) {
                $detail->delete();
            }
        }
        return Response()->json($bill->id);
    }
    public function updateFractionMode(Request $request)
    {
        $bill_detail = Bill_detail::find($request->id);
        if (!$bill_detail) {
            return response()->json(['error' => 'Detalle no encontrado'], 404);
        }
        $product = Product::find($bill_detail->id_product);
        $bill = Bill::find($bill_detail->id_bill);

        if ($request->mode === 'COMPLETO') {
            if ($bill->IVA == 1) {
                $price = $product->price + ($product->price * 0.16);
            } else {
                $price = $product->price;
            }
            $discount =  $price * ($bill_detail->discount_percent / 100);
            $net_amount =  $price - $discount;
            $bill_detail->code = $product->code;
            $bill_detail->name = $product->name;
            $bill_detail->price = $price;
            $bill_detail->priceU = $price - $discount;
            $bill_detail->quantity = 1;
            $bill_detail->total_amount =  $price;
            $bill_detail->discount = $discount;
            $bill_detail->net_amount =  $net_amount;
            $bill_detail->save();
        } else if ($request->mode === 'FRACCION') {
            if ($bill->IVA == 1) {
                $price_fraction = $product->price_fraction + ($product->price_fraction * 0.16);
            } else {
                $price_fraction = $product->price_fraction;
            }
            $discount =  $price_fraction * ($bill_detail->discount_percent / 100);
            $bill_detail->name = $product->name_fraction;
            $net_amount =  $price_fraction - $discount;
            $bill_detail->code = $product->code_fraction;
            $bill_detail->price = $price_fraction;
            $bill_detail->priceU = $price_fraction - $discount;
            $bill_detail->quantity = 1;
            $bill_detail->total_amount =  $price_fraction;
            $bill_detail->discount = $discount;
            $bill_detail->net_amount =  $net_amount;
            $bill_detail->save();
        }
        return response()->json(['success' => true]);
    }
    public function updateBillDetailInventory(Request $request)
    {
        $bill = Bill::where('id_seller', auth()->id())->where('status', 0)->first();
        $bill_detail = Bill_detail::where('id_bill', $bill->id)
            ->where('id_product', $request->id_product)
            ->first();
        if ($bill_detail) {
            $bill_detail->id_inventory = $request->id_inventory;
            $bill_detail->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
