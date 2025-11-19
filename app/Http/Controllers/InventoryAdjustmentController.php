<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\DataTables\InventoryAdjustmentDataTable;
use App\Models\InventoryAdjustment;

class InventoryAdjustmentController extends Controller
{
     public function indexInventoryAdjustment(InventoryAdjustmentDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('products.inventary');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function indexStocktaking(){
    if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
        $products = Product::where('status',1)->get();
        $categories = Category::where('status', '1')->get();
        $inventories = \App\Models\Inventory::where('status', 1)->get();
        return view('products.stocktaking', compact('products','categories','inventories'));
    } else {
        return redirect()->route('indexStore');
    }      
}

public function ajaxStocktaking(Request $request)
{
    DB::statement("SET SQL_MODE=''");
    $id_inventory = $request->get('id_inventory');
    if (request()->ajax()) {
        return datatables()->of(DB::table('products')
            ->leftJoin('stocks', function($join) use ($id_inventory) {
                $join->on('stocks.id_product','=','products.id')
                     ->where('stocks.id_inventory', '=', $id_inventory)
                     ->whereRaw('stocks.created_at = (SELECT MAX(created_at) FROM stocks s2 WHERE s2.id_product = products.id AND s2.id_inventory = ?)', [$id_inventory]);
            })
            ->select(
                'products.id as id',
                'products.name as name',
                'products.code as code',
                DB::raw('COALESCE(stocks.quantity,0) as stock'),
                DB::raw('FORMAT(products.price, 2) as price')
            )
        )->make(true);
    }
    return redirect()->route('indexStore');
}

public function storeStocktaking(Request $request)
{
    $inventoryAdjustment = InventoryAdjustment::create([
        'id_user' => auth()->id(),
        'id_inventory' => $request->id_inventory,
        'description' => $request->descriptionStock,
        'amount_lost' => $request->amountLost,
        'amount_profit' => $request->amountProfit,
        'amount' => $request->totalAmount,
    ]);
    foreach ($request->datos as $dato) {
        $diferenciaAbsoluta = abs($dato['diferencia']);
        Stock::create([
            'id_inventory_adjustment' => $inventoryAdjustment->id,
            'id_inventory' => $request->id_inventory,
            'id_product' => $dato['id_producto'],
            'id_user' => auth()->id(),
            'addition' => $dato['diferencia'] > 0 ? $diferenciaAbsoluta : 0,
            'subtraction' => $dato['diferencia'] < 0 ? $diferenciaAbsoluta : 0,
            'quantity' => $dato['nuevo_stock'],
            'description' => $request->descriptionStock,
        ]);
    }
    return response()->json($inventoryAdjustment->id);
}

public function stocktakingReset(Request $request)
{
    $inventoryAdjustment = InventoryAdjustment::create([
        'id_user' => auth()->id(),
        'id_inventory' => $request->id_inventory,
        'description' => $request->descriptionStock,
        'amount_lost' => $request->amountLost,
        'amount_profit' => $request->amountProfit,
        'amount' => $request->totalAmount,
    ]);
    $stocks = Stock::select('stocks.*')
        ->join(DB::raw('(SELECT id_product, MAX(created_at) as last_created_at FROM stocks WHERE id_inventory = ? GROUP BY id_product) as subquery'), function ($join) use ($request) {
            $join->on('stocks.id_product', '=', 'subquery.id_product')
                ->on('stocks.created_at', '=', 'subquery.last_created_at');
        })
        ->where('stocks.id_inventory', $request->id_inventory)
        ->setBindings([$request->id_inventory], 'select')
        ->get();
    foreach ($stocks as $stock) {
        $quantity = $stock->quantity;
        if ($quantity != 0) {
            $diferenciaAbsoluta = abs($quantity);
            Stock::create([
                'id_inventory_adjustment' => $inventoryAdjustment->id,
                'id_inventory' => $request->id_inventory,
                'id_product' => $stock->id_product,
                'id_user' => auth()->id(),
                'addition' => $quantity < 0 ? $diferenciaAbsoluta : 0,
                'subtraction' => $quantity > 0 ? $diferenciaAbsoluta : 0,
                'quantity' => 0,
                'description' => 'RESETEO DE INVENTARIO',
            ]);
        }
    }
    return response()->json(['success' => true]);
}
}
