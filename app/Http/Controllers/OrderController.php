<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Cart;
use App\Models\User;
use App\Models\Discount;
use App\Models\DetaillCart;
use App\Models\Employee;
use App\Models\Dolar;
use App\Models\Playment;
use App\Models\Seller;
use App\Models\SellerPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller{
    public function indexOrder(){
        DB::statement("SET SQL_MODE=''");
        if (auth()->user()->type == 'EMPRESA') {
            $employees = DB::table('users')
                ->join('employees', 'employees.id_employee','=','users.id')
                ->select('users.id as id','employees.id_company as id_company',
                'status','name', 'last_name','nationality','ci')
                ->where('status',1)
                ->where('id_company',auth()->id())
                ->get();
            $sellers = DB::table('users')
                ->join('sellers', 'sellers.id_seller','=','users.id')
                ->select('users.id as id','sellers.id_company as id_company',
                'status','name', 'last_name','nationality','ci')
                ->where('status',1)
                ->where('id_company',auth()->id())
                ->get();
            return view('orders.order', compact('employees','sellers'));
        } else if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'ADMINISTRATIVO' ||  auth()->user()->type == 'SUPERVISOR' ||auth()->user()->type == 'VENDEDOR') {
            return view('orders.order');
        } else {
            return redirect()->route('storeIndex');
        }
    }
    public function indexOrderClient(){
        return view('orders.orderClient');
    }
    public function ajaxOrderClient(){
        DB::statement("SET SQL_MODE=''");
        if(request()->ajax()) {
            return datatables()->of(DB::table('carts')
            ->join('users as company', 'company.id','=','carts.id_company')
            ->join('users as seller', 'seller.id','=','carts.id_seller')
            ->select('carts.id as id','carts.status as status','carts.id_client as id_client','carts.id_company as id_company',
            'carts.retiro as retiro','carts.additional as additional','carts.total as total','carts.order_date as order_date',
            'company.phone as phone' ,'seller.name as name', 'seller.last_name as last_name','seller.type as type')
            ->where('carts.status','!=','ACTIVO')
            ->where('carts.id_client',auth()->id()))
            ->addColumn('action', 'orders.orderClient-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return view('index');
    }
    public function ajaxOrder(){
        DB::statement("SET SQL_MODE=''");
        if ( auth()->user()->type == 'ADMINISTRADOR'){
            if(request()->ajax()) {
                return datatables()->of(DB::table('carts')
                ->join('users as seller', 'seller.id','=','carts.id_seller')
                ->select('carts.id as id','carts.status as status','carts.id_client as id_client','carts.id_company as id_company',
                    'carts.retiro as retiro','carts.additional as additional','carts.total as total','carts.order_date as order_date',
                    'carts.phone as phone' ,'seller.name as name', 'seller.last_name as last_name','seller.type as type')
                ->where('carts.status','!=','ACTIVO'))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else if ( auth()->user()->type == 'EMPRESA'){
            if(request()->ajax()) {
                return datatables()->of(DB::table('carts')
                ->join('users as seller', 'seller.id','=','carts.id_seller')
                ->select('carts.id as id','carts.status as status','carts.id_client as id_client','carts.id_company as id_company',
                    'carts.retiro as retiro','carts.additional as additional','carts.total as total','carts.order_date as order_date',
                    'carts.phone as phone' ,'seller.name as name', 'seller.last_name as last_name','seller.type as type')
                ->where('carts.status','!=','ACTIVO')
                ->where('carts.id_company',auth()->id()))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        }  else if ( auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'ADMINISTRATIVO' ||  auth()->user()->type == 'SUPERVISOR'){
            if(request()->ajax()) {
                return datatables()->of(DB::table('carts')
                ->join('users as seller', 'seller.id','=','carts.id_seller')
                ->select('carts.id as id','carts.status as status','carts.id_client as id_client','carts.id_company as id_company',
                    'carts.retiro as retiro','carts.additional as additional','carts.total as total','carts.order_date as order_date',
                    'carts.phone as phone' ,'seller.name as name', 'seller.last_name as last_name','seller.type as type')
                ->where('carts.status','!=','ACTIVO')
                ->where('carts.id_seller', auth()->id()))
                ->addColumn('action', 'orders.order-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        }  else if ( auth()->user()->type == 'VENDEDOR'){
            if(request()->ajax()) {
                return datatables()->of(DB::table('carts')
                ->join('users as seller', 'seller.id','=','carts.id_seller')
                ->select('carts.id as id','carts.status as status','carts.id_client as id_client','carts.id_company as id_company',
                    'carts.retiro as retiro','carts.additional as additional','carts.total as total','carts.order_date as order_date',
                    'carts.phone as phone' ,'seller.name as name', 'seller.last_name as last_name','seller.type as type')
                ->where('carts.status','!=','ACTIVO')
                ->where('carts.id_seller', auth()->id()))
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
        $playment = Playment::where('id_cart',$request->id)->first();
        return Response()->json(['playment' => $playment,'status' => $cart, 'data' => $detaillcart, 'success' => 'bien']);
    }
    public function summaryOrder(Request $request){   
        $cart = Cart::where('id',$request->id)->first();
        $detaillcart = DetaillCart::where('id_cart',$request->id)->get();
        $summary = 0;
        foreach ($detaillcart as $detaill) {
            $summary = $summary + ($detaill->quantity * $detaill->price);
        }
        $discount = $summary * ( $cart->discount_percent /100);
        $total =  $summary - $discount;
        $cart->update([
            'subtotal' =>  number_format($summary, 2),
            'discount' =>  number_format($discount, 2),
            'total'  => number_format($total, 2)
        ]); 
        $additional = $cart->additional;
        $totalAdditional = $total + $additional;
        $totalBs = $totalAdditional * $cart->dolar;
        return Response()->json([ 
            'totalBs' => number_format($totalBs, 2), 
            'totalAdditional' => number_format($totalAdditional, 2), 
            'subtotal' => number_format($summary, 2),  
            'discount' => number_format($discount, 2), 
            'additional' => number_format($additional, 2)
        ]); 
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
            'order_date' =>  date('Y-m-d H:i:s'),
            'status'  => $request->status
        ]); 
        if ($request->status == 'FINALIZADO') {
            $user = User::where('id',$cart->id_seller)->first();
            if ($user) {
                if ($user->type == 'VENDEDOR') {
                    $seller = Seller::where('id_seller',$user->id)->first();
                    $total_payment = $cart->total * ($seller->percent/100);
                    $sellerPayment = SellerPayment::Create([
                        'id_cart' =>  $cart->id, 
                        'id_seller' =>  $user->id, 
                        'total_cart' => $cart->total,
                        'percent' => $seller->percent,
                        'total_payment' => number_format($total_payment, 2),
                    ]); 
                }  
            } 
        }
        return Response()->json($cart);    
    } 
    public function ajustar(Request $request){ 
        $cart = Cart::where('id',$request->id)->first();
        $cart->update([ 
            'additional'  => $request->additional
        ]); 

        return Response()->json($cart);    
    } 
    public function addSeller(Request $request){ 
        $request->validate([
            'seller' => 'required'
        ]);
        $cart = Cart::where('id',$request->id_cart_seller)->first();
        $cart->update([ 
            'id_seller'  => $request->seller
        ]); 
        return Response()->json($cart);    
    }
    public function addAllSeller(){ 
        $carts = Cart::where('id_seller', auth()->id())
            ->whereNotIn('status', ['ACTIVO', 'FINALIZADO', 'RECHAZADO', 'INCONCLUSO'])
            ->get();
        if ($carts->isEmpty()) {
            return response()->json(['message' => 'no tienes ordenes que asignar']);
        }
        $employees = DB::table('users')
            ->join('employees', 'employees.id_employee', '=', 'users.id')
            ->where('employees.id_company',auth()->id())
            ->where('users.status', 1)
            ->select('employees.id_employee as id_employee')
            ->get();
        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No active employees available']);
        }
        $employees = $employees->map(function ($employee) use ($carts) {
            $employee->pendingJobCount = Cart::where('id_company', auth()->id())
                ->where('id_seller', $employee->id_employee)
                ->whereNotIn('status', ['ACTIVO', 'FINALIZADO', 'RECHAZADO', 'INCONCLUSO'])
                ->count();
            return $employee;
        })->sortBy('pendingJobCount');
    
        $totalCarts = $carts->count();
        $cartsPerEmployee = floor($totalCarts / $employees->count()); // Integer division
        $assignedCount = 0;
        foreach ($carts as $cart) {
            if ($assignedCount >= $totalCarts) {
                break; // Stop assigning if all carts are assigned
            }
    
            $leastLoadedEmployee = $employees->shift(); // Efficiently select employee with least jobs
            if ($leastLoadedEmployee) {
                $cart->update(['id_seller' => $leastLoadedEmployee->id_employee]);
                $assignedCount++;
            }
        }
        return Response()->json(['bien'=>'trabajos asignados']);    
    }
}
