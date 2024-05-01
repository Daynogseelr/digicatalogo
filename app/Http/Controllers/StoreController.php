<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Cart;
use App\Models\DetaillCart;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class StoreController extends Controller{
    public function storeIndex(){
        $companies = User::where('type','EMPRESA')->where('status','1')->get();
        return view('stores.companies', compact('companies'));
    }
    public function indexStore(Request $request){ 
        DB::statement("SET SQL_MODE=''");
        $categories = DB::table('categories')
        ->join('add_categories', 'add_categories.id_category','=','categories.id')
        ->select('categories.id as id', 'name')
        ->where('add_categories.id_company',$request->id_company)
        ->groupBy('add_categories.id_category')
        ->get();
        $id_company = $request->id_company;
        $id_cart = Cart::where('id_client',auth()->id())->where('status','ACTIVO')->first();
        $quantity = DetaillCart::where('id_cart',$id_cart->id)->sum('quantity');
        $products = Product::search($request->scope)->where('id_company',$request->id_company)->paginate(48);
        if ($request->ajax()) {
            if ($request->category == '' || $request->category == 'TODAS'){
                $products = Product::search($request->scope)->where('id_company',$request->id_company)->paginate(48);
            } else {
                $products = DB::table('products')
                ->where('products.name','like',"%$request->scope%")
                ->where('products.id_company',$request->id_company)
                ->join('add_categories', 'add_categories.id_product','=','products.id')
                ->where('add_categories.id_category',$request->category)
                ->select('products.id as id','products.id_company as id_company','url1','name','price','status')
                ->paginate(48);
            }
            return response()->json(view('stores.products', compact('products'))->render()); 
        }
        return view('stores.store', compact('products','id_cart','quantity','categories','id_company'));
    }
    public function indexCart(){
        $products = Product::all();
        return view('stores.cart', compact('products'));
    }
}
