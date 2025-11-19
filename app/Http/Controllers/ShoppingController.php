<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Shopping;
use App\Models\Stock;
use App\Models\Currency;
use App\Models\Serial;
use App\DataTables\ShoppingDataTable;

class ShoppingController extends Controller
{
    public function index(ShoppingDataTable $dataTable)
    {
        return $dataTable->render('products.shopping');
    }
    public function indexAddShopping(Request $request)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $products = Product::where('status',1)->get();
            $categories = Category::where('status', '1')->get();
            $inventories = Inventory::where('status',1)->get();
            // Monedas principales y secundarias con tasa de cambio activa
            $currencyPrincipal = Currency::where('is_principal', 1)->first();
            $currencies = Currency::where('status', 1)->get();
            return view('products.addShopping', compact(
                'products','categories','inventories',
                'currencyPrincipal','currencies'
            ));
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function storeShopping(Request $request)
    {
        $request->validate([
            'id_inventory' => 'required',
            'codeBill' => 'required|string|max:100',
            'date' => 'required|date',
            'nameProvider' => 'required|string|max:200',
            'totalBill' => 'required|numeric',
            'productsTableData' => 'required|array',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        // Crear nueva compra
        $shopping = new Shopping();
        $shopping->id_user = auth()->id();
        $shopping->id_inventory = $request->id_inventory;
        $shopping->codeBill = $request->codeBill;
        $shopping->date = $request->date;
        $shopping->name = $request->nameProvider;
        $shopping->total = $request->totalBill;
        $shopping->save();

        // Guardar productos y seriales
        $seriales = json_decode($request->input('serials', '{}'), true);
        foreach ($request->productsTableData as $productData) {
            $product = Product::find($productData['id']);
            if ($product) {
                $quantity = $productData['quantity'];
                if ($quantity > 0) {
                    $lastStock = Stock::where('id_product', $product->id)
                        ->where('id_inventory', $request->id_inventory)
                        ->latest()->first();
                    $newQuantity = $lastStock ? $lastStock->quantity + $quantity : $quantity;
                    $stock = new Stock();
                    $stock->id_product = $product->id;
                    $stock->id_user = auth()->id();
                    $stock->id_inventory = $request->id_inventory;
                    $stock->id_shopping = $shopping->id;
                    $stock->cost =$productData['cost'];;
                    $stock->addition = $quantity;
                    $stock->subtraction = 0;
                    $stock->quantity = $newQuantity;
                    $stock->description = 'COMPRA FACTURA '. $request->codeBill;
                    $stock->save();
                }
                $product->cost = $productData['cost'];
                $product->utility = $productData['utility'];
                $product->price = $productData['price'];
                $product->save();
                if (isset($seriales[$product->id]) && is_array($seriales[$product->id])) {
                    foreach ($seriales[$product->id] as $serialValue) {
                        $serial = new Serial();
                        $serial->id_shopping = $shopping->id;
                        $serial->id_product = $product->id;
                        $serial->serial = $serialValue;
                        $serial->status = 0;
                        $serial->save();
                    }
                }

            }
        }

        return response()->json([
            'id' => $shopping->id,
            'message' => 'Compra registrada correctamente'
        ]);
    }
    public function codeProduct()
    {
        $product = Product::orderByDesc('code')
            ->latest()
            ->first();
        if ($product) {
            return Response()->json($product->code + 1);
        } else {
            return Response()->json(1);
        }
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
}