<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\DetaillCart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetaillCartController extends Controller{
    public function addCart(Request $request){ 
        $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
       $existe = DetaillCart::where('id_product',$request->id_product)
       ->where('id_cart',$cart->id)->first();
       if ($existe) {
            return Response()->json(['success' => 'error']);
       } else {
            if ($request->quantity < 1 || $request->quantity == '') {
                $quantity = 1;
            } else {
                $quantity = $request->quantity;
            }
            $cart->detaill_carts()->create([
                'id_product' => $request->id_product,
                'quantity' => $quantity,
                'price' => $request->price,
           ]);
           return Response()->json(['success' => 'success']);
       }
    }
    public function mostrarCart(Request $request){   
        $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DB::table('detaill_carts')
            ->join('products', 'products.id','=','detaill_carts.id_product')
            ->select(DB::raw("*"))
            ->where('detaill_carts.id_cart',$cart->id)->get();  
        $existe = DetaillCart::where('id_cart',$cart->id)->first();
        if ($existe) {
            return Response()->json([ 'data' => $detaillcart, 'success' => 'bien']);
        } else {
            return Response()->json([ 'data' => $detaillcart, 'success' => 'error']);
        }
    }
    public function summaryCart(Request $request){   
        $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DetaillCart::where('id_cart',$cart->id)->get();
        $summary = 0;
        foreach ($detaillcart as $detaill) {
            $summary = $summary + ($detaill->quantity * $detaill->price);
        }
        return Response()->json($summary); 
    }
    public function updateCart(Request $request){ 
         $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DetaillCart::where('id_product',$request->id)
        ->where('id_cart',$cart->id);
        $detaillcart->update(['quantity' => $request->quantity]); 
        return Response()->json($detaillcart);    
    }
    public function deleteCart(Request $request){ 
         $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DetaillCart::where('id_product',$request->id)
        ->where('id_cart',$cart->id)->delete();
        return Response()->json($cart->id);    
    }
    public function quantityCart(Request $request){
        $cart = Cart::where('id_client',auth()->id())
        ->where('id_company',$request->id_company)
        ->where('status','ACTIVO')->first();
        $detaillcart = DetaillCart::where('id_cart',$cart->id)->sum('quantity');
        return Response()->json($detaillcart);  
    }
    public function buscarProduct(Request $request){
        $product = Product::where('name','like', '%'.$request->buscar.'%')->get();
        return Response()->json($product);  
    }
}