<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\DetaillCart;
use App\Models\Category;
use App\Models\User;
use App\Models\Seller;
use App\Models\Dolar;
use App\Models\Currency;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    function indexStore(Request $request)
    {
        $inventories = Inventory::select('id', 'name')->where('status', 1)->get();
        DB::statement("SET SQL_MODE=''");
        $categories = Category::select('id', 'name')
            ->where('status', 1)
            ->get();
        $products = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'url1', 'name', 'status')
            ->where('status', '!=', '0')
            ->orderByDesc('updated_at')
            ->paginate(42);
        // Obtener moneda principal
        $currencies = Currency::where('status', 1)->get();
        
        return view('stores.store', compact(
            'categories',
            'inventories',
            'currencies',
        ));
    }
   public function indexStoreAjax(Request $request)
{
    DB::statement("SET SQL_MODE=''");

    $products = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'url1','type', 'name', 'status')
        ->where('status', '!=', '0');

    if ($request->category != '' && $request->category != 'TODAS') {
        $products->join('add_categories', 'add_categories.id_product', '=', 'products.id')
            ->where('add_categories.id_category', $request->category);
    }

    if ($request->scope != '') {
        $products->where('products.name', 'like', "%$request->scope%");
    }

    $selectedInventoryId = $request->input('id_inventory');
    if ($selectedInventoryId && $selectedInventoryId != 'all') {
        $products->whereHas('stocks', function ($query) use ($selectedInventoryId) {
            $query->where('id_inventory', $selectedInventoryId);
        });
    }
    // Si es "all", no filtra por inventario ni por stock

    if ($request->has('sort_by') && in_array($request->sort_by, ['asc', 'desc', 'available', 'unavailable'])) {
        $products = $this->sortProductsByStock($products, $request->sort_by, ($selectedInventoryId != 'all' ? $selectedInventoryId : null));
    } else {
        $products->orderByDesc('products.updated_at');
    }

    $products = $products->paginate(42);

    foreach ($products as $product) {
        if ($selectedInventoryId && $selectedInventoryId != 'all') {
            $latestStock = DB::table('stocks')
                ->where('id_product', $product->id)
                ->where('id_inventory', $selectedInventoryId)
                ->latest()
                ->first();
            $product->stock = $latestStock ? $latestStock->quantity : 0;
        } else {
            // Suma de los últimos stocks de cada inventario
            $inventories = Inventory::where('status', 1)->get();
            $totalStock = 0;
            foreach ($inventories as $inv) {
                $latestStock = DB::table('stocks')
                    ->where('id_product', $product->id)
                    ->where('id_inventory', $inv->id)
                    ->latest()
                    ->first();
                $totalStock += $latestStock ? $latestStock->quantity : 0;
            }
            $product->stock = $totalStock;
        }
    }

    $currencies = Currency::where('status', 1)->get();
    $currencyPrincipal = Currency::where('is_principal', 1)->first();
    $currencyOfficial = Currency::where('is_official', 1)->first();
    $currencySelected = Currency::find($request->id_currencyStore);

    $inventories = Inventory::select('id', 'name')->where('status', 1)->get();

    return response()->json(
        view('stores.products', compact(
            'products',
            'currencies',
            'currencyPrincipal',
            'currencyOfficial',
            'currencySelected',
            'inventories'
        ))->render()
    );
}

    private function sortProductsByStock($products, $sortBy, $selectedInventoryId = null)
    {
        // Asegurarse de que la subconsulta de stock use el id_inventory seleccionado
        // Y que el ORDER BY sea robusto para casos donde no hay stock (NULL)
        $stockSubquery = '(SELECT quantity FROM stocks
                        WHERE id_product = products.id
                        ' . ($selectedInventoryId ? 'AND id_inventory = ' . (int)$selectedInventoryId : '') . '
                        ORDER BY created_at DESC LIMIT 1)';

        $products->addSelect(DB::raw($stockSubquery . ' as stock_value')); // Usar un alias diferente para evitar conflictos

        switch ($sortBy) {
            case 'asc':
                // Ordena los productos con stock 0 o NULL al final
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) ASC");
                break;
            case 'desc':
                // Ordena los productos con stock 0 o NULL al final
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) DESC");
                break;
            case 'available':
                $products->whereRaw($stockSubquery . ' > 0');
                break;
            case 'unavailable':
                $products->whereRaw($stockSubquery . ' < 1'); // Incluye 0 y NULL
                break;
        }

        return $products;
    }
    public function mostrarProduct(Request $request)
{
    $product = Product::find($request->id);

    $inventories = Inventory::select('id', 'name')->where('status', 1)->get();

    if ($request->id_inventory && $request->id_inventory != 'all') {
        $latestStock = DB::table('stocks')
            ->where('id_product', $product->id)
            ->where('id_inventory', $request->id_inventory)
            ->latest()
            ->first();
        $product->stock = $latestStock ? $latestStock->quantity : 0;
    } else {
        // Suma de los últimos stocks de cada inventario
        $totalStock = 0;
        foreach ($inventories as $inv) {
            $latestStock = DB::table('stocks')
                ->where('id_product', $product->id)
                ->where('id_inventory', $inv->id)
                ->latest()
                ->first();
            $totalStock += $latestStock ? $latestStock->quantity : 0;
        }
        $product->stock = $totalStock;
    }

    $currencyPrincipal = Currency::where('is_principal', 1)->first();
    $currencyOfficial = Currency::where('is_official', 1)->first();
    $currencySelected = Currency::find($request->id_currencyStore);

    return response()->json([
        'product' => $product,
        'currencyOfficial' => $currencyOfficial,
        'currencyPrincipal' => $currencyPrincipal,
        'currencySelected' => $currencySelected,
        'inventories' => $inventories,
    ]);
}
public function getIntegralInfo(Request $request)
{
    $product = Product::find($request->id);
    $integrals = DB::table('product_integrals')
        ->join('products', 'products.id', '=', 'product_integrals.id_product')
        ->where('product_integrals.id_productI', $product->id)
        ->select('products.name', 'product_integrals.quantity', 'products.price')
        ->get();
    $abbrPrincipal = Currency::where('is_principal', 1)->value('abbreviation');
    $currencySelected = Currency::find(request('id_currencyStore'));
    return response()->json([
        'product' => $product,
        'integrals' => $integrals,
        'abbrPrincipal' => $abbrPrincipal,
        'currencySelected' => $currencySelected,
    ]);
}

// FRACCIONADO
public function getFractionInfo(Request $request)
{
    $product = Product::find($request->id);
    $abbrPrincipal = Currency::where('is_principal', 1)->value('abbreviation');
    $currencySelected = Currency::find(request('id_currencyStore'));
    return response()->json([
        'product' => $product,
        'abbrPrincipal' => $abbrPrincipal,
        'currencySelected' => $currencySelected,
    ]);
}
}
