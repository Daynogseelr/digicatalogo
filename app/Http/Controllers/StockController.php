<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Dolar;
use App\Models\Shopping;
use App\Models\Serial;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function indexStock($id){
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $product = Product::find($id);
            return view('products.stock', compact('product'));
        } else {
            return redirect()->route('indexStore');
        }      
    }
   
    public function indexShopping(){
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return view('products.shopping');
        } else {
            return redirect()->route('indexStore');
        }      
    }
    public function indexAddShopping(){
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $products = Product::where('status',1)->get();
            $categories = Category::where('status', '1')->get();
            $inventories = Inventory::where('status',1)->get();
            return view('products.addShopping', compact('products','categories','inventories'));
        } else {
            return redirect()->route('indexStore');
        }      
    }
    public function ajaxStock($id_product){
        DB::statement("SET SQL_MODE=''");
        $stocks = DB::table('stocks')
            ->join('users','users.id','=','stocks.id_user')
            ->select('stocks.addition','stocks.subtraction','stocks.quantity','stocks.description','stocks.created_at',
                'users.name','users.last_name','users.nationality','users.ci')
            ->where('stocks.id_product', $id_product)
            ->orderBy('stocks.created_at', 'desc')
            ->get();
        if(request()->ajax()) {
            return datatables()->of($stocks)
                // No need to add formatted_created_at here
                ->addIndexColumn()
                ->make(true);
        }
        return view('indexStore');
    }
    
   
    public function ajaxShopping()
    {
        DB::statement("SET SQL_MODE=''");
        if (auth()->user()->type == 'ADMINISTRADOR') {
            if (request()->ajax()) {
                return datatables()->of(Shopping::select('*'))
                    ->make(true);
            }
        } else  if (auth()->user()->type == 'EMPRESA') {
            if (request()->ajax()) {
                return datatables()->of(Shopping::select('*')->where('id_company',auth()->id())->where('status',1))
                    ->addColumn('action', 'products.shopping-action')
                    ->addIndexColumn()
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } else  if (auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $companies = Employee::where('id_employee', auth()->id())->first();
            $id_company = $companies->id_company;
            if (request()->ajax()) {
                return datatables()->of(Shopping::select('*')->where('id_company', $id_company)->where('status',1))
                    ->addColumn('action', 'products.shopping-action')
                    ->addIndexColumn()
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
        return view('index');
    }
    public function storeStock(Request $request)
    {
        $request->validate([
            'stocks'  => 'required',
            'descriptions'  => 'required',
        ]);
        if (auth()->user()->type == 'EMPLEADO' || auth()->user()->type == 'SUPERVISOR' || auth()->user()->type == 'ADMINISTRATIVO') {
            $id_company = Employee::select('id_company')->where('id_employee',auth()->id())->first();
            $id_compan = $id_company->id_company;
        } else {
            $id_compan = auth()->id();
        }
        $stock = Stock::where('id_product',$request->id_product)
            ->latest()
            ->first();
        if ($request->status == 'Reponer') {
            if ($stock) {
                $quantity = $stock->quantity + $request->stocks;
            }else {
                $quantity = $request->stocks;
            }
            $stocknew   =   Stock::Create(
                [
                    'id_company' => $id_compan,
                    'id_product' => $request->id_product,
                    'id_user' => auth()->id(),
                    'addition' => $request->stocks,
                    'subtraction' => 0,
                    'quantity' => $quantity,
                    'description' => $request->descriptions,
                ]
            );
        } else {
            if ($stock) {
                $quantity = $stock->quantity - $request->stocks;
            } else {
                $quantity = 0 - $request->stocks;
            }
            $stocknew   =   Stock::Create(
                [
                    'id_company' => $id_compan,
                    'id_product' => $request->id_product,
                    'id_user' => auth()->id(),
                    'addition' => 0,
                    'subtraction' => $request->stocks,
                    'quantity' => $quantity,
                    'description' => $request->descriptions,
                ]
            );
        }
        
        return Response()->json($stocknew);
    }
 
    public function addProductShopping(Request $request)
    {
        $products = [];
        if ($request->id_product && is_array($request->id_product) && count($request->id_product) > 0) {
            foreach ($request->id_product as $id_product) {
                $product = Product::find($id_product);
                if ($product) { // Check if the product exists
                    $products[] = $product;
                }
            }
        }
        return response()->json(['products' => $products]);
    }
    public function storeShopping(Request $request)
    {
        // Validar los datos del formulario (opcional, pero recomendado)
        $request->validate([
            'id_inventory' => 'required',
            'codeBill' => 'required|string|max:100',
            'date' => 'required|date',
            'nameProvider' => 'required|string|max:200',
            'totalBill' => 'required|numeric',
            'productsTableData' => 'required|array',
        ]);
        // Crear nueva compra
        $shopping = new Shopping(); 
        $shopping->id_inventory = $request->id_inventory;
        $shopping->codeBill = $request->codeBill;
        $shopping->date = $request->date;
        $shopping->name = $request->nameProvider;
        $shopping->total = $request->totalBill;
        $shopping->save();
        // Guardar o actualizar productos de la tabla
        foreach ($request->productsTableData as $productData) {
            $product = Product::find($productData['id']);
            if ($product) {
                $quantity = $productData['quantity'];
                if ($quantity > 0) {
                    // Buscar el último registro de stock para el producto
                    $lastStock = Stock::where('id_product', $product->id)->where('id_inventory', $request->id_inventory)->latest()->first();
                    if ($lastStock) {
                        // Actualizar el stock sumando la cantidad anterior
                        $newQuantity = $lastStock->quantity + $quantity;
                        $stock = new Stock();
                        $stock->id_product = $product->id;
                        $stock->id_user =  auth()->id();
                        $stock->id_inventory = $request->id_inventory;
                        $stock->addition = $quantity;
                        $stock->subtraction = 0;
                        $stock->quantity = $newQuantity;
                        $stock->description = 'COMPRA FACTURA '. $request->codeBill;
                        $stock->save();
                    } else {
                        // Crear un nuevo registro de stock
                        $stock = new Stock();
                        $stock->id_product = $product->id;
                        $stock->id_user =  auth()->id();
                        $stock->id_inventory = $request->id_inventory;
                        $stock->addition = $quantity;
                        $stock->subtraction = 0;
                        $stock->quantity = $quantity;
                        $stock->description = 'COMPRA FACTURA '. $request->codeBill;
                        $stock->save();
                    }
                }
                $product->cost = $productData['cost'];
                $product->utility = $productData['utility'];
                $product->price = $productData['price'];
                $product->save();
            }
        }
        return response()->json(['code' => $codeNew,'message' => 'Compra registrada correctamente']);
    }
    public function verifySerialOld(Request $request)
    {
        $shopping = Shopping::where('id_user', auth()->id())
            ->where('status', 0)->first();
        $serial = Serial::where('id_product', $request->id_product)->where('id_shopping', $shopping->id)->first();
        if ($serial) {
            $serial = Serial::where('id_product', $request->id_product)->where('id_shopping', $shopping->id)->delete();
            return response()->json(['message' => 'Seriales eliminados']);
        } else {
            return response()->json(['message' => 'no']);
        }
    }
    public function verifySerialNew(Request $request)
    {
        $shopping = Shopping::where('id_user', auth()->id())
            ->where('status', 0)->first();
        $product = Product::find($request->id_product);
        if ($product->serial == 1) {
            $serial = Serial::where('id_product', $request->id_product)->where('id_shopping', $shopping->id)->count();
            if ($serial > 0) {
                $serials = Serial::where('id_product', $request->id_product)->where('id_shopping', $shopping->id)->get();
                $quantity =  $request->quantity - $serial;
                return response()->json(['message' => 'si hay seriales', 'quantity' => $quantity, 'serials' => $serials, 'product' => $product]);
            } else {
                return response()->json(['message' => 'no hay seriales', 'quantity' => $request->quantity, 'product' => $product]);
            }
        } else {
            return response()->json(['message' => 'no usa seriales']);
        }
    }
    public function storeSerials(Request $request)
    {
        $serial = Serial::where('id_product', $request->id_product)->where('id_shopping', $shopping->id)->delete();
        foreach ($request->serials as $serials) {
            $serial = new Serial();
            $serial->id_shopping = $shopping->id;
            $serial->id_product = $request->id_product;
            $serial->serial = $serials['serial'];
            $serial->status = 0;
            $serial->save();
        }
        $serialNew = Serial::where('id_shopping',$shopping->id)->where('id_product',$request->id_product)->count();
        return response()->json(['message' => 'seriales guardados', 'quantity' =>  $serialNew]);
    }
}
