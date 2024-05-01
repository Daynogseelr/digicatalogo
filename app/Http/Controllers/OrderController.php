<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Cart;
use App\Models\DetaillCart;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller{
    public function indexOrder(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPLEADO') {
            return view('orders.order');
        } else {
            return redirect()->route('storeIndex');
        } 
    }
    public function indexOrderClient(){
        if (auth()->user()->type == 'CLIENTE') {
            return view('orders.orderClient');
        } else {
            return redirect()->route('dashboard');
        }  
    }
    public function ajaxOrderClient(){
        if(request()->ajax()) {
            return datatables()->of(Cart::select('*')->where('status','!=','ACTIVO')
            ->where('id_client',auth()->id()))
            ->addColumn('action', 'orders.orderClient-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return view('index');
    }
    public function ajaxOrder(){
        if ( auth()->user()->type == 'ADMINISTRADOR'){
            if(request()->ajax()) {
                return datatables()->of(Cart::select('*')->where('status','!=','ACTIVO'))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else if ( auth()->user()->type == 'EMPRESA'){
            if(request()->ajax()) {
                return datatables()->of(Cart::select('*')->where('status','!=','ACTIVO')->where('id_company',auth()->id()))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        }  else if ( auth()->user()->type == 'EMPLEADO'){
            $id_company = Employee::select('id_company')->where('id_employee',auth()->id())->first();
            if(request()->ajax()) {
                return datatables()->of(Cart::select('*')->where('status','!=','ACTIVO')->where('id_company', $id_company->id_company))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        }
        
        return view('index');
    }
    public function mostrarOrder(Request $request){   
        $detaillcart = DB::table('detaill_carts')
            ->join('products', 'products.id','=','detaill_carts.id_product')
            ->select('url1', 'name', 'description', 'quantity','detaill_carts.price as price', 'detaill_carts.id as id')
            ->where('detaill_carts.id_cart',$request->id)->get();
        $cart = Cart::select('status')->where('id',$request->id)->first();
        return Response()->json([ 'status' => $cart, 'data' => $detaillcart, 'success' => 'bien']);
    }
    public function summaryOrder(Request $request){   
        $cart = Cart::where('id',$request->id)->first();
        $detaillcart = DetaillCart::where('id_cart',$request->id)->get();
        $summary = 0;
        foreach ($detaillcart as $detaill) {
            $summary = $summary + ($detaill->quantity * $detaill->price);
        }
        $cart->update([ 
            'total'  => $summary 
        ]); 
        return Response()->json($summary); 
    }
    public function updateOrder(Request $request){ 
        $id_cart =  DetaillCart::select('id_cart')->where('id',$request->id)->first();
        $detaillcart = DetaillCart::where('id',$request->id);
        $detaillcart->update(['quantity' => $request->quantity]); 
        return Response()->json($id_cart);    
    }
    public function deleteOrder(Request $request){ 
        $id_cart =  DetaillCart::select('id_cart')->where('id',$request->id)->first();
        $detaillcart = DetaillCart::where('id',$request->id)->delete();
        return Response()->json($id_cart);    
    }
    public function statusOrder(Request $request){ 
        $cart = Cart::where('id',$request->id)->first();
        $cart->update([ 
            'status'  => $request->status
        ]); 
        return Response()->json($cart);    
    } 
}
