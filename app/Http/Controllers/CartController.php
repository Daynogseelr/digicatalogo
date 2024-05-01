<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Cart;
use App\Models\DetaillCart;
use Illuminate\Http\Request;
use App\Http\Controllers\StoreController;
class CartController extends Controller{
    public function mostrarProduct(Request $request){   
        $where = array('id' => $request->id);
        $product  = Product::where($where)->first();
        return Response()->json($product);
    }
    public function storeCart(Request $request){
        $request->validate([
            'retiro' => 'required' 
        ]);   
        $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DetaillCart::where('id_cart',$cart->id)->get();
        $summary = 0;
        foreach ($detaillcart as $detaill) {
            $summary = $summary + ($detaill->quantity * $detaill->price);
        }
        $cart->update([
            'status' => 'PENDIENTE', 
            'id_client' =>  auth()->id(), 
            'order_date' =>  date('Y-m-d H:i:s'),
            'retiro' =>  $request->retiro,
            'total'  => $summary 
        ]); 
        $carts   =   Cart::Create([
            'id_client' =>  auth()->id(), 
            'id_company' =>  $request->id_company, 
        ]); 
        return Response()->json($cart);     
    }
}
