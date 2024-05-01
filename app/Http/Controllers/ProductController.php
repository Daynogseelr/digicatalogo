<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\User;
use Datatables;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\If_;

class ProductController extends Controller{
    public function indexProduct(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR') {
            $companies = User::where('type', 'EMPRESA')->get();
            return view('products.product', compact('companies'));
        } else  if ( auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }      
    }
    public function ajaxProduct(){
        if (auth()->user()->type == 'ADMINISTRADOR') {
            if(request()->ajax()) {
                return datatables()->of(Product::select('*'))
                ->addColumn('action', 'products.product-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else  if (auth()->user()->type == 'EMPRESA' ){
            if(request()->ajax()) {
                return datatables()->of(Product::select('*')->where('id_company',auth()->id()))
                ->addColumn('action', 'products.product-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } 
        
        return view('index');
    }
    public function storeProduct(Request $request){  
        $productId = $request->id; 
        if ($productId == NULL || $productId == '') {
            $request->validate([
                'name' => 'required|min:2|max:50|string|unique:products,name',
                'description'  => 'required|min:5|max:300|string',
                'price'  => 'required',
            ]);   
        } else {
            $product = Product::find($request-> id);
            if (! $product) {
                abort(404);
            }
            $request->validate([
                'name' => 'required|min:2|max:50|string|unique:products,name,'. $product->id,
                'description'  => 'required|min:5|max:200|string',
                'price'  => 'required',
            ]);   
        }
        if ($request->file('url')) {
            $product   =   Product::updateOrCreate(
                [
                 'id' => $productId
                ],
                [
                'id_company' =>  auth()->id(), 
                'name' => $request->name, 
                'description' => $request->description,
                'price'  => $request->price,
                'status' => '1'
            ]); 
            $i=0;
            foreach ($request->url as $imagen) {
                $i++;
                $ruta='products/';
                $imagenProduct= date('YmdHis')."".$i.".".$imagen->getClientOriginalExtension();
                $imagen->move($ruta,$imagenProduct);
                $url= $imagenProduct;
                $product->update([
                    'url'.$i =>  $url
                ]); 
            }
        } else {
            $product   =   Product::updateOrCreate(
                [
                 'id' => $productId
                ],
                [
                'id_company' =>  auth()->id(), 
                'name' => $request->name, 
                'description' => $request->description,
                'price'  => $request->price,
                'status' => '1'
            ]); 
        }
        return Response()->json($product);
    }
    public function editProduct(Request $request){   
        $where = array('id' => $request->id);
        $product  = Product::where($where)->first();
        return Response()->json($product);
    }
    public function destroyProduct(Request $request){
        $product = Product::where('id',$request->id)->delete();
        return Response()->json($product);
    }
    public function statusProduct(Request $request){ 
        $product = Product::find($request-> id);
        if ( $product->status == '0') {
            $product->update(['status' => '2']); 
        } elseif ( $product->status == '2') {
            $product->update(['status' => '1']); 
        } else {
            $product->update(['status' => '0']); 
        }
        return Response()->json($product);   
    }
}
