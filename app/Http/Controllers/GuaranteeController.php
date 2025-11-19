<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Guarantee;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuaranteeController extends Controller
{
    public function indexGuarantee(){
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'SUPERVISOR' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'EMPLEADO') {
            $products = DB::table('products')->get();
            $inventories = Inventory::where('status',1)->get();
            return view('guarantees.guarantee', compact('products','inventories'));
        } else {
            return redirect()->route('indexStore');
        }      
    }
    public function ajaxGuarantee(){
        if(request()->ajax()) {
            return datatables()->of(Guarantee::select('*')->orderBy('created_at','desc'))
            ->addColumn('action', 'guarantees.guarantee-action')
            ->rawColumns(['action'])
            ->addColumn('formatted_created_at', function ($closure) {
                return $closure->created_at->format('d/m/Y H:i:s'); // Adjust the format as needed
            })
            ->addIndexColumn()
            ->make(true);
        }
         return redirect()->route('indexStore');
    }
    public function modalGuarantee(Request $request)
    {
        $product = Product::find($request->id);
        return Response()->json($product);
    }
    public function mostrarGuarantee(Request $request)
    {
        $guarantee = Guarantee::find($request->id);
        return Response()->json($guarantee);
    }
    public function statusGuarantee(Request $request)
    {
        $guarantee = Guarantee::find($request->id);
        $guarantee->status =  $request->status;
        $guarantee->save();
        if ($request->status == 'REPARADO') {
            $stock = Stock::where('id_product', $guarantee->id_product)
            ->where('id_inventory', $request->id_inventory)
            ->latest()
            ->first();
            if ($stock) {
                Stock::create([
                    'id_product' => $guarantee->id_product,
                    'id_user' => auth()->id(),
                    'id_inventory' => $request->id_inventory,
                    'addition' => 1,
                    'subtraction' => 0,
                    'quantity' => $stock->quantity + 1,
                    'description' => 'Reparación de producto',
                ]);
            }
        }
        return Response()->json($guarantee);
    }
    public function sendGuarantee(Request $request)
    {
        // Valida los datos recibidos
        $validatedData = $request->validate([
            'id_product' => 'required|integer',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'serial' => 'nullable|string',
        ]);
        $product = Product::find($validatedData['id_product']);
        $guarantee = new Guarantee();
        $guarantee->id_product = $product->id;
        $guarantee->code = $product->code;
        $guarantee->name = $product->name;
        if (isset($validatedData['serial'])) {
            $guarantee->serial = $validatedData['serial'];
        } else {
            $guarantee->serial = null; // O cualquier valor predeterminado que desees
        }
        $guarantee->description = $validatedData['description'];
        $guarantee->status = $validatedData['status'];
        $guarantee->save();
       
        return response()->json(['id_billDetaill' => $request->id_billDetaill,'id_guarantee' => $guarantee->id]);
    }
    public function storeGuarantee(Request $request)
    {
        $request->validate([
            'id_product'  => 'required',
            'status'  => 'required',
            'description'  => 'required',
        ]);
        $product = Product::find($request->id_product);
        $guarantee = new Guarantee();
        $guarantee->id_product =  $product->id;
        $guarantee->code =  $product->code;
        $guarantee->name =  $product->name;
        $guarantee->serial =  $request->serial;
        $guarantee->description =  $request->description;
        $guarantee->status =  $request->status;
        $guarantee->save();
        $stock = Stock::where('id_product', $product->id)
        ->where('id_inventory', $request->id_inventory)
        ->latest()
        ->first();
        if ($stock) {
            Stock::create([
                'id_product' => $product->id,
                'id_user' => auth()->id(),
                'id_inventory' => $request->id_inventory,
                'addition' => 0,
                'subtraction' => 1,
                'quantity' => $stock->quantity - 1,
                'description' => 'Garantia de producto',
            ]);
        }
        return Response()->json($guarantee);
    }
}
